<?php

namespace App\Services\Favourable;

use App\Libraries\Pinyin;
use App\Models\AdminUser;
use App\Models\Cart;
use App\Models\Category;
use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Models\MerchantsShopInformation;
use App\Models\RegionStore;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Common\CommonManageService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 积分管理
 * Class JigonService
 * @package App\Services\Erp
 */
class FavourableManageService
{
    protected $merchantCommonService;
    protected $dscRepository;
    protected $categoryService;
    protected $commonManageService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        CategoryService $categoryService,
        CommonManageService $commonManageService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->categoryService = $categoryService;
        $this->commonManageService = $commonManageService;
    }

    /*
     * 取得优惠活动列表
     * @return   array
     */
    public function favourableList($ru_id, $rs_id = 0)
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'favourableList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        /* 过滤条件 */
        $filter['keyword'] = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['is_going'] = empty($_REQUEST['is_going']) ? 0 : 1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['use_type'] = empty($_REQUEST['use_type']) ? 0 : intval($_REQUEST['use_type']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);

        $res = FavourableActivity::whereRaw(1);
        //ecmoban模板堂 --zhuo start
        if ($filter['use_type'] == 1) { //自营
            $res = $res->where('user_id', 0)->where('userFav_type', 0);
        } elseif ($filter['use_type'] == 2) { //商家
            $res = $res->where('user_id', 0)->where('userFav_type', 0);
        } elseif ($filter['use_type'] == 3) { //全场
            $res = $res->where('userFav_type', 1);
        } elseif ($filter['use_type'] == 4) { //商家自主使用
            $res = $res->where('user_id', $ru_id)->where('userFav_type', 0);
        } else {
            if ($ru_id > 0) {
                $res = $res->where(function ($query) use ($ru_id) {
                    $query->where('user_id', $ru_id)->orWhere('userFav_type', 1);
                });
            }
        }
        //ecmoban模板堂 --zhuo end
        if ($rs_id) {
            $rs_mer = $this->getRsMer($rs_id);
            if ($rs_mer) {
                $res = $res->where(function ($query) use ($rs_id, $rs_mer) {
                    $rs_mer = BaseRepository::getExplode($rs_mer);
                    $query->where('rs_id', $rs_id)->orWhereIn('user_id', $rs_mer);
                });
            } else {
                $res = $res->where('rs_id', $rs_id);
            }
        }

        if ($filter['review_status']) {
            $res = $res->where('review_status', $filter['review_status']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] != 0) {
            if ($ru_id == 0) {
                $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                if ($filter['store_search'] == 1) {
                    $res = $res->where('user_id', $filter['merchant_id']);
                }

                if ($filter['store_search'] > 1) {
                    $store_search = $filter['store_search'] ?? '';
                    $store_keyword = $filter['store_keyword'] ?? '';

                    $res = $res->where(function ($query) use ($store_type, $store_search, $store_keyword) {
                        $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($store_type, $store_search, $store_keyword) {
                            if ($store_search == 2) {
                                $query = $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($store_keyword) . '%');
                            }
                            if ($store_search == 3) {
                                $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($store_keyword) . '%');
                                if ($store_type) {
                                    $query->where('shop_name_suffix', $store_type);
                                }
                            }
                        });
                    });
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end
        //区分商家和自营
        if (!empty($filter['seller_list'])) {
            $res = CommonRepository::constantMaxId($res, 'user_id');
        } else {
            $res = $res->where('user_id', 0);
        }
        if (!empty($filter['keyword'])) {
            $res = $res->where('act_name', 'LIKE', '%' . mysql_like_quote($filter['keyword']) . '%');
        }
        if ($filter['is_going']) {
            $now = TimeRepository::getGmTime();
            $res = $res->where('start_time', '<=', $now)->where('end_time', '>=', $now);
        }

        $filter['record_count'] = $res->count();

        /* 分页大小 */
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 查询 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])->offset($filter['start'])->limit($filter['page_size']);
        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $row['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['start_time']);
                $row['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['end_time']);
                $row['user_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
                if ($row['rs_id']) {
                    $row['rs_name'] = $this->getRsName($row['rs_id']);
                }
                $list[] = $row;
            }
        }

        return ['item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    // 同一类型优惠范围（分类或品牌） -qin
    public function getActRangeExt($act_range, $act_id)
    {
        $admin_id = $this->commonManageService->getAdminId();

        $now = TimeRepository::getGmTime();
        // 商家id
        $user_id = AdminUser::where('user_id', $admin_id)->value('ru_id');

        $res = FavourableActivity::select('act_range_ext');

        if ($act_range > 0) {
            $res = $res->where('act_range', $act_range);
        }

        $res = $res->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('act_id', '<>', $act_id)
            ->where('user_id', $user_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr = array_merge($arr, explode(',', $row['act_range_ext']));
        }

        return array_unique($arr);
    }

    // 获取分类或品牌下得所有商品
    public function getRangeGoods($act_range, $act_range_ext_list, $create_in, $user_id = 0)
    {
        if (empty($act_range_ext_list)) {
            return [];
        }

        switch ($act_range) {
            case FAR_CATEGORY:
                $id_list = [];
                foreach ($act_range_ext_list as $id) {
                    /**
                     * 当前分类下的所有子分类
                     */
                    $cat_keys = $this->categoryService->getArrayKeysCat(intval($id));

                    $id_list = array_merge($id_list, $cat_keys);
                }
                break;
            case FAR_BRAND:
                $id_list = $act_range_ext_list;
                break;
            case FAR_GOODS:
                $id_list = $act_range_ext_list;
                break;

            default:
                break;
        }

        $id_list = BaseRepository::getExplode($id_list);
        $res = Goods::select('goods_id')->where('user_id', $user_id)->whereIn($create_in, $id_list);
        $res = BaseRepository::getToArrayGet($res);

        $arr_goods_id = [];
        foreach ($res as $row) {
            $arr_goods_id[] = $row['goods_id'];
        }
        return $arr_goods_id;
    }

    /*
    * 获取卖场列表 卖场促销 liu
    */
    public function getRsList()
    {
        $pin = new Pinyin();

        $res = RegionStore::select('rs_name', 'rs_id');
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $k => $val) {
                $res[$k]['rs_name'] = $val['rs_name'];
                $res[$k]['letter'] = strtoupper(substr($pin->Pinyin($val['rs_name'], EC_CHARSET), 0, 1));
            }
        }
        return $res;
    }

    /*
    * 搜索卖场结果列表 卖场促销 liu
    */
    public function getRsListSearch($where = '', $search = '', $leftjoin = '')
    {
        $filter['record_count'] = $where->count();

        $filter = page_and_size($filter);

        $where = $where->offset($filter['start'])->limit($filter['page_size']);
        $rs_list = BaseRepository::getToArrayGet($where);

        $filter['page_arr'] = seller_page($filter, $filter['page']);
        return ['list' => $rs_list, 'filter' => $filter];
    }

    /*
    * 获取商家列表 卖场促销 liu
    */
    public function getMerchantsList($rs_id = 0)
    {
        $res = RegionStore::with(['getRsRegion' => function ($query) {
            $query->with(['getMerchantsShopInformationList' => function ($rr_query) {
                $rr_query->select('region_id', 'shop_id', 'user_id');
            }]);
        }]);
        $res = $res->where('rs_id', $rs_id);
        $res = BaseRepository::getToArrayFirst($res);
        if (isset($res) && !empty($res['get_rs_region']['get_merchants_shop_information_list'])) {
            $res = $res['get_rs_region']['get_merchants_shop_information_list'];
        } else {
            $res = [];
        }

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $k => $val) {
                $res[$k]['shop_name'] = $merchantList[$val['user_id']]['shop_name'] ?? '';
            }
        }
        return $res;
    }

    /*
    * 搜索商家结果列表 卖场促销 liu
    */
    public function getMerList($mer_ids = '', $keyword = '', $admin_rs_id = '')
    {
        $res = RegionStore::select('rs_id');
        $res = $res->with(['getRsRegion' => function ($query) use ($mer_ids, $keyword) {
            $query->select('rs_id', 'region_id');
            $query->with(['getMerchantsShopInformationList' => function ($msi_query) use ($mer_ids, $keyword) {
                $msi_query->select('region_id', 'shop_id', 'user_id');

                if ($keyword) {
                    $msi_query->with(['sellershopinfo' => function ($ssi_query) use ($keyword) {
                        $ssi_query->where('shop_name', 'LIKE', '%' . $keyword . '%');
                    }]);
                }
            }]);
        }]);
        if ($admin_rs_id) {
            $res = $res->where('rs_id', $admin_rs_id);
        }
        $mer_list = BaseRepository::getToArrayFirst($res);

        if (isset($mer_list) && !empty($mer_list['get_rs_region']['get_merchants_shop_information_list'])) {
            $mer_list = $mer_list['get_rs_region']['get_merchants_shop_information_list'];
        } else {
            $mer_list = [];
        }

        $filter['record_count'] = count($mer_list);

        $filter = page_and_size($filter);

        $filter['page_arr'] = seller_page($filter, $filter['page']);
        return ['list' => $mer_list, 'filter' => $filter];
    }

    /*
    * 通过卖场ID获取卖场名称 卖场促销 liu
    */
    public function getRsName($rs_id)
    {
        $rs_name = RegionStore::where('rs_id', $rs_id)->value('rs_name');
        $rs_name = $rs_name ?? '';
        return $rs_name;
    }

    /*
    * 通过卖场ID获取卖场管理的商家ID 卖场促销 liu
    */
    public function getRsMer($rs_id = 0)
    {
        $res = MerchantsShopInformation::select('user_id');
        $res = $res->whereHasIn('getRsRegion', function ($query) use ($rs_id) {
            $query->whereHasIn('getRegionStore', function ($rs_query) use ($rs_id) {
                $rs_query->where('rs_id', $rs_id);
            });
        });
        $res = BaseRepository::getToArrayGet($res);
        if ($res) {
            $res = BaseRepository::getFlatten($res);
        }

        return $res;
    }

    //查询商家所有分类
    public function getUserCatList($ru_id)
    {
        $user_cat = MerchantsShopInformation::where('user_id', $ru_id)->value('user_shop_main_category');
        $arr = $new_arr = [];
        if (!empty($user_cat)) {
            $user_cat = explode(" - ", $user_cat);

            foreach ($user_cat as $key => $row) {
                $arr[$key] = explode(":", $row);
            }

            foreach ($arr as $key => $row) {
                foreach ($row as $ck => $rows) {
                    if ($ck > 0) {
                        $arr[$key][$ck] = explode(",", $rows);
                    }
                }
            }

            $arr = $this->getLevelThreeCat1($arr);
            $arr = arr_foreach($arr);
            $arr = array_unique($arr);

            foreach ($arr as $key => $row) {
                $new_arr[$key]['id'] = $row;
                $new_arr[$key]['name'] = Category::where('cat_id', $row)->value('cat_name');
            }

            $new_arr = get_array_sort($new_arr, 'id');
            return $new_arr;
        }
    }

    public function getUserCatSearch($ru_id, $keyword = '', $arr = [])
    {
        $res = Category::select('cat_id', 'cat_name');
        $res = $res->whereHasIn('getMerchantsCategory', function ($query) use ($ru_id) {
            $query->where('user_id', $ru_id);
        });
        $res = BaseRepository::getToArrayGet($res);

        $arr = array_values($arr);

        if ($res) {
            $arr = array_merge($arr, $res);
        }

        $new_arr = [];
        if (!empty($keyword)) {
            foreach ($arr as $key => $row) {
                $pos = strpos($row['name'], $keyword);
                if ($pos === false) {
                    unset($row);
                } else {
                    $new_arr[$key] = $row;
                }
            }
        } else {
            $new_arr = $arr;
        }

        return $new_arr;
    }

    public function getLevelThreeCat1($arr)
    {
        $new_arr = [];

        foreach ($arr as $key => $row) {
            $new_arr[$key]['cat'] = $row[0];
            $new_arr[$key]['cat_child'] = $row[1];
            $new_arr[$key]['cat_child_three'] = $this->getLevelThreeCat2($row[1]);
        }

        foreach ($new_arr as $key => $row) {
            $new_arr[$key] = array_values($row);
        }

        return $new_arr;
    }

    public function getLevelThreeCat2($arr)
    {
        $new_arr = [];

        foreach ($arr as $key => $row) {
            $new_arr[$key] = $this->getCatListThree($row);
        }

        $new_arr = arr_foreach($new_arr);
        return $new_arr;
    }

    public function getCatListThree($arr)
    {
        $res = Category::select('cat_id')->where('parent_id', $arr);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        foreach ($res as $key => $row) {
            $arr[$key] = $row['cat_id'];
        }

        return $arr;
    }

    /**
     * 营销中心促销活动列表
     * @return array
     */
    public static function marketing_center()
    {
        $group_list = [
            [
                'name' => trans('admin::common.platform_activity'),
                'child' => [
                    [
                        'name' => trans('admin::common.01_wholesale'),
                        'desc' => trans('admin::common.wholesale_notic'),
                        'url' => 'wholesale.php?act=list',
                        'data_param' => 'menushopping|13_wholesale',
                        'icon' => 'icon-wholesale',
                        'class' => 'hide', // 是否隐藏 class="hide"
                    ],
                    [
                        'name' => trans('admin::common.09_topic'),
                        'desc' => trans('admin::common.topic_notic'),
                        'url' => 'topic.php?act=list',
                        'data_param' => 'menushopping|09_topic',
                        'icon' => 'icon-home-renovation',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.12_favourable'),
                        'desc' => trans('admin::common.favourable_notic'),
                        'url' => 'favourable.php?act=list',
                        'data_param' => 'menushopping|12_favourable',
                        'icon' => 'icon-discount',
                        'class' => '',
                    ],
                ]
            ],
            [
                'name' => trans('admin::common.trade_play'),
                'child' => [
                    [
                        'name' => trans('admin::common.auction'),
                        'desc' => trans('admin::common.auction_notic'),
                        'url' => 'auction.php?act=list',
                        'data_param' => 'menushopping|10_auction',
                        'icon' => 'icon-auction',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.02_snatch_list'),
                        'desc' => trans('admin::common.snatch_list_notic'),
                        'url' => 'snatch.php?act=list',
                        'data_param' => 'menushopping|02_snatch_list',
                        'icon' => 'icon-tag-alt',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.group'),
                        'desc' => trans('admin::common.group_notic'),
                        'url' => 'group_buy.php?act=list',
                        'data_param' => 'menushopping|08_group_buy',
                        'icon' => 'icon-group-alt',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.03_seckill_list'),
                        'desc' => trans('admin::common.seckill_list_notic'),
                        'url' => 'seckill.php?act=list',
                        'data_param' => 'menushopping|03_seckill_list',
                        'icon' => 'icon-seckill',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.integral_mall'),
                        'desc' => trans('admin::common.integral_mall_notic'),
                        'url' => 'exchange_goods.php?act=list',
                        'data_param' => 'menushopping|15_exchange_goods',
                        'icon' => 'icon-exchange-alt',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.presale'),
                        'desc' => trans('admin::common.presale_notic'),
                        'url' => 'presale.php?act=list',
                        'data_param' => 'menushopping|16_presale',
                        'icon' => 'icon-presale',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.09_crowdfunding'),
                        'desc' => trans('admin::common.crowdfunding_notic'),
                        'url' => 'zc_project.php?act=list',
                        'data_param' => 'menushopping|09_crowdfunding',
                        'icon' => 'icon-crown',
                        'class' => '',
                    ],
                    // [
                    //     'name' => trans('admin::common.10_lottery'),
                    //     'desc' => trans('admin::common.lottery_notic'),
                    //     'url' => 'lottery.php?act=index',
                    //     'data_param' => 'menushopping|10_lottery',
                    //     'icon' => 'icon-crown',
                    //     'class' => '',
                    // ]
                ]
            ],
            [
                'name' => trans('admin::common.bonus_card_coupons'),
                'child' => [
                    [
                        'name' => trans('admin::common.bonus'),
                        'desc' => trans('admin::common.bonus_notic'),
                        'url' => 'bonus.php?act=list',
                        'data_param' => 'menushopping|04_bonustype_list',
                        'icon' => 'icon-bonus',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.14_package_list'),
                        'desc' => trans('admin::common.package_list_notic'),
                        'url' => 'package.php?act=list',
                        'data_param' => 'menushopping|14_package_list',
                        'icon' => 'icon-package-two',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.17_coupons'),
                        'desc' => trans('admin::common.coupons_notic'),
                        'url' => 'coupons.php?act=list',
                        'data_param' => 'menushopping|17_coupons',
                        'icon' => 'icon-coupon',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.18_value_card'),
                        'desc' => trans('admin::common.value_card_notic'),
                        'url' => 'value_card.php?act=list',
                        'data_param' => 'menushopping|18_value_card',
                        'icon' => 'icon-value-card',
                        'class' => '',
                    ],
                    [
                        'name' => trans('admin::common.gift_gard_manage'),
                        'desc' => trans('admin::common.gift_gard_manage_notic'),
                        'url' => 'gift_gard.php?act=list',
                        'data_param' => 'menushopping|gift_gard_list',
                        'icon' => 'icon-gift-card',
                        'class' => '',
                    ],
                ]
            ]
        ];

        return $group_list;
    }

    /**
     * 更新购物车
     *
     * @param array $actIdList
     */
    public function updateCart($actIdList = [])
    {
        if ($actIdList) {

            $actIdList = BaseRepository::getExplode($actIdList);

            /* 更新购物车商品 */
            Cart::whereIn('act_id', $actIdList)->update([
                'act_id' => 0
            ]);

            /* 删除赠品 */
            Cart::whereIn('is_gift', $actIdList)->delete();
        }

    }
}
