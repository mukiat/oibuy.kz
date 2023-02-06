<?php

use App\Libraries\Pager;
use App\Libraries\Shop;
use App\Models\AdminUser;
use App\Models\AppealImg;
use App\Models\Article;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CollectGoods;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\ComplaintImg;
use App\Models\ComplainTitle;
use App\Models\ComplaintTalk;
use App\Models\DiscussCircle;
use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsLibCat;
use App\Models\GoodsReportTitle;
use App\Models\GoodsReportType;
use App\Models\IntelligentWeight;
use App\Models\MerchantsCategory;
use App\Models\MerchantsNav;
use App\Models\MerchantsShopInformation;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\Region;
use App\Models\RegionWarehouse;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\UserRank;
use App\Models\Users;
use App\Models\UsersReal;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Services\Ads\AdsService;
use App\Services\Cart\CartCommonService;
use App\Services\Category\CategoryBrandService;
use App\Services\Category\CategoryService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\User\UserRankService;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Goods\GoodsDataHandleService;

/**
 * 获得指定分类同级的所有分类以及该分类下的子分类
 *
 * @access  public
 * @param integer $cat_id 分类编号
 * @return  array
 */
function get_categories_tree_pro($cat_id = 0, $type = '')
{
    $CategoryRep = app(CategoryService::class);

    if ($cat_id > 0) {
        $parent_id = Category::where('cat_id', $cat_id)->value('parent_id');
    } else {
        $parent_id = 0;
    }

    /*
      判断当前分类中全是是否是底级分类，
      如果是取出底级分类上级分类，
      如果不是取当前分类及其下的子分类
     */
    $cat_id = Category::where('parent_id', $parent_id)->where('is_show', 1)->value('cat_id');

    if ($cat_id || $parent_id == 0) {

        /* 获取当前分类及其子分类 */
        $res = Category::where('parent_id', $parent_id)
            ->where('is_show', 1)
            ->orderBy('sort_order')
            ->orderBy('cat_id');

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $row) {
                $cat_id = $row['cat_id'];

                if ($row['parent_id'] == 0) {
                    $cat_name = '';
                    for ($i = 1; $i <= $GLOBALS['_CFG']['auction_ad']; $i++) {
                        $cat_name .= "'cat_tree_" . $row['cat_id'] . "_" . $i . "',";
                    }

                    $cat_name = substr($cat_name, 0, -1);

                    $cat_arr[$row['cat_id']]['ad_position'] = app(AdsService::class)->getAdPostiChild($cat_name);
                }

                /* 子分类 */
                if ($cat_id > 0) {
                    $children = $CategoryRep->getCatListChildren($cat_id);
                } else {
                    $children = [];
                }

                $cat = Category::where('cat_id', $cat_id);
                $cat = BaseRepository::getToArrayFirst($cat);

                /* 获取分类下文章 */
                $articles = Article::whereHasIn('getArticleCat', function ($query) use ($row) {
                    $query->where('cat_name', $row['cat_name']);
                });
                $articles = $articles->orderByRaw("article_type, article_id desc");
                $articles = $articles->take(4);
                $articles = BaseRepository::getToArrayGet($articles);

                if ($articles) {
                    foreach ($articles as $key => $val) {
                        $articles[$key]['url'] = $val['open_type'] != 1 ?
                            app(DscRepository::class)->buildUri('article', ['aid' => $val['article_id']], $val['title']) : trim($val['file_url']);
                    }
                }

                /* 平台品牌筛选 */
                $brands_list = app(CategoryBrandService::class)->getCatBrand([], $children, 0, 0, 0, [], [], ['sort_order', 'brand_id'], 'asc');

                if ($brands_list) {
                    foreach ($brands_list as $key => $val) {
                        $temp_key = $key;
                        $brands_list[$temp_key]['brand_name'] = $val['brand_name'];
                        $brands_list[$temp_key]['url'] = app(DscRepository::class)->buildUri('category', ['cid' => $cat_id, 'bid' => $val['brand_id']], $cat['cat_name']);
                        $brands_list[$temp_key]['selected'] = 0;
                    }
                }

                $cat_arr[$row['cat_id']]['brands'] = $brands_list;
                $cat_arr[$row['cat_id']]['articles'] = $articles;

                if ($row['is_show']) {
                    //by guan start
                    if ($row['parent_id'] == 0 && !empty($row['category_links'])) {
                        if (empty($type)) {
                            $cat_name_arr = explode('、', $row['cat_name']);
                            if (!empty($cat_name_arr)) {
                                $category_links_arr = explode("\r\n", $row['category_links']);
                            }

                            $cat_name_str = "";
                            foreach ($cat_name_arr as $cat_name_key => $cat_name_val) {
                                $link_str = $category_links_arr[$cat_name_key];

                                $cat_name_str .= '<a href="' . $link_str . '" target="_blank">' . $cat_name_val;

                                if (count($cat_name_arr) == ($cat_name_key + 1)) {
                                    $cat_name_str .= '</a>';
                                } else {
                                    $cat_name_str .= '</a>、';
                                }
                            }

                            $cat_arr[$row['cat_id']]['name'] = $cat_name_str;
                            $cat_arr[$row['cat_id']]['category_link'] = 1;
                            $cat_arr[$row['cat_id']]['oldname'] = $row['cat_name']; //by EcMoban-weidong   保留原生元素
                        } else {
                            $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                            $cat_arr[$row['cat_id']]['oldname'] = $row['cat_name']; //by EcMoban-weidong   保留原生元素
                        }
                    } else {
                        $cat_arr[$row['cat_id']]['name'] = $row['cat_name'];
                    }
                    //by guan end

                    $cat_arr[$row['cat_id']]['id'] = $row['cat_id'];

                    $cat_arr[$row['cat_id']]['url'] = app(DscRepository::class)->buildUri('category', ['cid' => $row['cat_id']], $row['cat_name']);

                    if (isset($row['cat_id']) != null) {
                        $cat_arr[$row['cat_id']]['cat_id'] = app(CategoryService::class)->getChildTreePro($row['cat_id']);
                    }
                }
            }
        }
    }

    if (isset($cat_arr)) {
        return $cat_arr;
    }
}

/**
 * 获得指定的商品属性
 *
 * @param array $arr 规格、属性ID数组
 * @param string $type 设置返回结果类型：pice，显示价格，默认；no，不显示价格
 * @return mixed|string
 */
function get_goods_attr_info_new($arr = [], $type = '')
{
    $attr = '';
    if (!empty($arr)) {
        if ($type == 'pice') {
            $fmt = "%s:%s[%s] \n";
        } else {
            $fmt = "%s:%s \n";
        }

        $arr = !is_array($arr) ? explode(",", $arr) : $arr;
        $res = GoodsAttr::whereIn('goods_attr_id', $arr);

        $res = $res->with([
            'getGoodsAttribute' => function ($query) {
                $query->select('attr_id', 'attr_name', 'sort_order');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $val) {
                $attribute = $val['get_goods_attribute'];

                $res[$key]['attr_id'] = $attribute['attr_id'];
                $res[$key]['attr_name'] = $attribute['attr_name'];
                $res[$key]['sort_order'] = $attribute['sort_order'];
            }


            $result = [];
            foreach ($res as $key => $value) {
                $result[$value['attr_name']][] = $value;
            }

            $ret = [];
            foreach ($result as $key => $value) {
                array_push($ret, $value);
            }

            foreach ($ret as $k => $v) {
                $res[$k]['attr_id'] = $v[0]['attr_id'];
                $res[$k]['attr_name'] = $v[0]['attr_name'];
                $res[$k]['sort_order'] = $v[0]['sort_order'];

                $v = BaseRepository::getSortBy($v, 'attr_sort');

                $res[$k]['attr_key'] = $v;
            }

            $res = BaseRepository::getSortBy($res, 'sort_order');

            foreach ($res as $row) {
                if ($GLOBALS['_CFG']['goods_attr_price'] == 1) {
                    $attr_price = 0;
                } else {
                    $attr_price = round(floatval($row['attr_price']), 2);
                    $attr_price = app(DscRepository::class)->getPriceFormat($attr_price, false);
                }

                if ($type == 'pice') {
                    $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value'], $attr_price);
                } else {
                    $attr .= sprintf($fmt, $row['attr_name'], $row['attr_value']);
                }
            }

            $attr = str_replace('[0]', '', $attr);
        }
    }

    return $attr;
}

/* 评论百分比 */

function comment_percent($goods_id)
{
    $haoping_count = Comment::where('id_value', $goods_id)
        ->where('comment_type', 0)
        ->where('parent_id', 0)
        ->where('status', 1)
        ->where(function ($query) {
            $query->where('comment_rank', 4)
                ->orWhere('comment_rank', 5);
        })->count();

    $zhongping_count = Comment::where('id_value', $goods_id)
        ->where('comment_type', 0)
        ->where('parent_id', 0)
        ->where('status', 1)
        ->where(function ($query) {
            $query->where('comment_rank', 2)
                ->orWhere('comment_rank', 3);
        })->count();

    $chaping_count = Comment::where('id_value', $goods_id)
        ->where('comment_type', 0)
        ->where('parent_id', 0)
        ->where('status', 1)
        ->where('comment_rank', 1)->count();

    $comment_count = Comment::where('id_value', $goods_id)
        ->where('comment_type', 0)
        ->where('parent_id', 0)
        ->where('status', 1)->count();

    $comment_count = !empty($comment_count) ? $comment_count : 1;

    $arr['haoping_percent'] = substr(number_format(($haoping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
    $arr['zhongping_percent'] = substr(number_format(($zhongping_count / $comment_count) * 100, 2, '.', ''), 0, -1);
    $arr['chaping_percent'] = substr(number_format(($chaping_count / $comment_count) * 100, 2, '.', ''), 0, -1);

    if ($comment_count == 0) {
        $arr['haoping_percent'] = 100;
    }

    foreach ($arr as $key => $val) {
        if ($val <= 0) {
            $arr[$key] = 0;
        }
    }

    return $arr;
}

/**
 * 取得自定义导航栏列表
 * @param string $type 位置，如top、bottom、middle
 * @return  array         列表
 */
function get_merchants_navigator($ru_id = 0, $ctype = '', $catlist = [])
{
    $res = MerchantsNav::where('ru_id', $ru_id)
        ->where('ifshow', 1)
        ->orderByRaw('vieworder, type asc');

    $res = BaseRepository::getToArrayGet($res);

    $cur_url = substr(strrchr(request()->server('REQUEST_URI'), '/'), 1);

    if (intval($GLOBALS['_CFG']['rewrite'])) {
        if (strpos($cur_url, '-')) {
            preg_match('/([a-z]*)-([0-9]*)/', $cur_url, $matches);
            $cur_url = $matches[1] . '.php?id=' . $matches[2];
        }
    } else {
        $cur_url = substr(strrchr(request()->server('REQUEST_URI'), '/'), 1);
    }

    $noindex = false;
    $active = 0;
    $navlist = [
        'top' => [],
        'middle' => [],
        'bottom' => []
    ];

    if ($res) {
        foreach ($res as $row) {
            $navlist[$row['type']][] = [
                'cat_id' => 'cid',
                'cat_name' => $row['name'],
                'opennew' => $row['opennew'],
                'url' => $row['url'],
                'ctype' => $row['ctype'],
                'cid' => $row['cat_id'],
                'vieworder' => $row['vieworder'],
            ];
        }
    }

    /* 遍历自定义是否存在currentPage */
    if ($navlist && $navlist['middle']) {
        foreach ($navlist['middle'] as $k => $v) {
            $condition = isset($ctype) && $ctype ? ($v['url'] && strpos($cur_url, $v['url']) === 0 && strlen($cur_url) == strlen($v['url'])) : $v['url'] && (strpos($cur_url, $v['url']) === 0);
            if ($condition) {
                $navlist['middle'][$k]['active'] = 1;
                $noindex = true;
                $active += 1;
            }
            if (substr($v['url'], 0, 8) == 'category') {
                $cat_id = $v['cid'];
                $cat_list = app(CategoryService::class)->getCategoriesTreeXaphp($cat_id);
                $navlist['middle'][$k]['cat'] = 1;
                $navlist['middle'][$k]['cat_list'] = $cat_list;
            } elseif (substr($v['url'], 0, 15) == 'merchants_store') {
                if ($v['cid']) {
                    $build_uri = [
                        'cid' => $v['cid'],
                        'urid' => $ru_id,
                        'append' => $v['name']
                    ];

                    $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($ru_id, $build_uri);
                    $navlist['middle'][$k]['url'] = $domain_url['domain_name'];
                }
            }
        }
    }

    if ($catlist && !empty($ctype) && $active < 1) {
        foreach ($catlist as $key => $val) {
            if ($navlist && $navlist['middle']) {
                foreach ($navlist['middle'] as $k => $v) {
                    if (!empty($v['ctype']) && $v['ctype'] == $ctype && $v['cid'] == $val && $active < 1) {
                        $navlist['middle'][$k]['active'] = 1;
                        $noindex = true;
                        $active += 1;
                    }
                }
            }
        }
    }

    if ($noindex == false) {
        $navlist['config']['index'] = 1;
    }

    return $navlist;
}

//退换货的--换货属性查询
function get_user_attr_checked($goods_attr, $attr_id)
{
    $arr['class'] = 'catcolor';
    $arr['attr_val'] = '';
    if ($goods_attr) {
        foreach ($goods_attr as $key => $grow) {
            if ($grow == $attr_id) {
                $arr['class'] = 'cattsel';
                $arr['attr_val'] = $grow;
                return $arr;
            }
        }
    }

    return $arr;
}

/**
 * 获取地区信息
 *
 * @param array $region
 * @return string
 */
function get_area_region_info($region = [])
{
    /* 取得区域名 */
    return \App\Repositories\Region\RegionRepository::get_area_region_info($region);
}

/**
 * 取得用户等级信息
 * @access   public
 * @return array
 * @author   Xuan Yan
 *
 */
function get_rank_info()
{
    if (!empty(session('user_rank'))) {
        $row = UserRank::where('rank_id', session('user_rank'));
        $row = BaseRepository::getToArrayFirst($row);

        if (empty($row)) {
            return [];
        }

        if ($row['special_rank']) {
            return $row;
        } else {
            $user_id = session('user_id', 0);
            $rank_points = Users::where('user_id', $user_id)->value('rank_points');

            $rt = [];
            if ($rank_points) {
                $rt = UserRank::where('min_points', '>', $rank_points)
                    ->orderBy('min_points');
                $rt = BaseRepository::getToArrayFirst($rt);
            }

            if ($rt) {
                $next_rank_name = $rt['rank_name'];
                $next_rank = $rt['min_points'] - $rank_points;
            } else {
                $next_rank_name = '';
                $next_rank = 0;
            }

            $row['rank_sort'] = app(UserRankService::class)->getUserRankSort($row['rank_id']);
            $row['rank_points'] = $rank_points;
            $row['next_rank_name'] = $next_rank_name;
            $row['next_rank'] = $next_rank;
            return $row;
        }
    } else {
        return [];
    }
}

//晒单回复ajax
function single_show_reply_list($parent_id, $page)
{
    $record_count = Comment::where('parent_id', $parent_id)->where('single_id', '>', 0)->where('status', 1)->count();

    $size = 5;

    $pagerParams = [
        'total' => $record_count,
        'listRows' => $size,
        'pa' => "",
        'id' => $parent_id,
        'type' => 0,
        'page' => $page,
        'funName' => 'single_reply_gotoPage',
        'pageType' => 1,
        'libType' => 1
    ];
    $reply_comment = new Pager($pagerParams);
    $limit = $reply_comment->limit;
    $reply_paper = $reply_comment->fpage([0, 4, 5, 6, 9]);

    $comment = Comment::where('parent_id', $parent_id)->where('single_id', '>', 0)->where('status', 1)->orderBy('add_time', 'desc');

    $start = ($page - 1) * $size;

    if ($start > 0) {
        $comment = $comment->skip($start);
    }

    if ($size > 0) {
        $comment = $comment->take($size);
    }

    $comment = BaseRepository::getToArrayGet($comment);

    $comment_list = [];
    $replay_comment = [];
    if ($comment) {
        foreach ($comment as $key => $comm) {

            //判断引用的那个评论
            $child_comment = Comment::where('comment_id', $comm['parent_id']);
            $child_comment = BaseRepository::getToArrayFirst($child_comment);

            if ($child_comment) {
                $comment_list[$key]['quote_username'] = $child_comment['user_name'];
                $comment_list[$key]['quote_content'] = $child_comment['content'];
            }
            $comment_list[$key]['comment_id'] = $comm['comment_id'];
            $comment_list[$key]['content'] = $comm['content'];
            if (!empty($comm['add_time'])) {
                $comment_list[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $comm['add_time']);
            }
            if (!empty($comm['user_name'])) {
                $comment_list[$key]['user_name'] = $comm['user_name'];
            }
        }
    }

    $cmt = ['comment_list' => $comment_list, 'reply_paper' => $reply_paper, 'record_count' => $record_count];
    return $cmt;
}

//查询市下面是否还有区域
function get_isHas_area($parent_id = 0, $type = 0)
{
    if ($type == 0) {
        $region_id = Region::where('parent_id', $parent_id)->value('region_id');
        return $region_id;
    } elseif ($type == 1) {
        $res = Region::select('parent_id')
            ->where('region_id', $parent_id);
        $res = $res->with([
            'getRegionParent' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        $res = $res && $res['get_region_parent'] ? array_merge($res, $res['get_region_parent']) : $res;

        return $res;
    }
}

//判断商品是否被编辑，如有编辑，则设置为未审核
function get_goods_file_content($goods_id, $arr = '', $ru_id, $review_goods)
{
    if ($ru_id > 0) {
        if (!empty($arr)) {
            $arr = explode('-', $arr);
            $arr1 = $arr[0]; //商品信息
            $arr2 = $arr[1]; //仓库商品信息

            $arr1 = explode(',', $arr1);

            for ($i = 0; $i < count($arr1); $i++) {
                if ($arr1[$i] == 'promote_price') {
                    $contents = floatval($_POST[$arr1[$i]]);
                } else {
                    $contents = $_POST[$arr[$i]];
                }

                $res = Goods::where('goods_id', $goods_id)->value($arr1[$i]);

                if ($contents <> $res) {
                    $review_status = 1;

                    if ($GLOBALS['_CFG']['review_goods'] == 0) {
                        $review_status = 5;
                    } else {
                        if ($review_goods == 0) {
                            $review_status = 5;
                        }
                    }

                    if ($review_status < 3) {
                        Cart::where('goods_id', $goods_id)->delete();
                    }

                    $update = Goods::where('goods_id', $goods_id);
                    $update = CommonRepository::constantMaxId($update, 'user_id');
                    $update->update(['review_status' => $review_status]);

                    break;
                }
            }
        } else {
            Goods::where('goods_id', $goods_id)
                ->update(['review_status' => 3]);
        }
    }
}

/*
 * 生成随机字符
 * []abcdefghijklmnopqrstuvwxyz*\/|0123456789{}
 */

function mc_random($length, $char_str = 'abcdefghijklmnopqrstuvwxyz0123456789')
{
    $hash = '';
    $chars = $char_str;
    $max = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $hash .= substr($chars, (rand(0, 1000) % $max), 1);
    }
    return $hash;
}

//ecmoban模板堂 --zhuo end

/** 划分商家或平台运费 start*/

/**
 * 购物车商品
 * 【0：按仓库划分、1：按商家划分】
 *
 * @param $goods
 * @param int $type
 * @return array
 */
function get_cart_goods_ru_list($goods = [], $type = 0)
{
    $ru_id_list = BaseRepository::getKeyPluck($goods, 'ru_id');
    $ru_id_list = array_values(array_unique($ru_id_list));

    $arr = [];
    if ($ru_id_list) {
        foreach ($ru_id_list as $wkey => $ru) {
            $sql = [
                'where' => [
                    [
                        'name' => 'ru_id',
                        'value' => $ru
                    ]
                ]
            ];

            $arr[$ru] = BaseRepository::getArraySqlGet($goods, $sql);
        }
    }

    if ($type == 1) {
        /* 【1：按商家划分】 */
        return $arr;
    } else {
        /* 【0：按仓库划分】 */
        $new_arr = [];
        foreach ($arr as $key => $row) {
            $new_arr[$key] = get_cart_goods_warehouse_list($row);
        }

        return $new_arr;
    }
}

/**
 * 购物车商品【按仓库划分】
 *
 * @param array $goods
 * @return array
 */
function get_cart_goods_warehouse_list($goods = [])
{
    $warehouse_id_list = BaseRepository::getKeyPluck($goods, 'warehouse_id');
    $warehouse_id_list = array_values(array_unique($warehouse_id_list));

    $arr = [];
    if ($warehouse_id_list) {
        foreach ($warehouse_id_list as $wkey => $warehouse) {
            $sql = [
                'where' => [
                    [
                        'name' => 'warehouse_id',
                        'value' => $warehouse
                    ]
                ]
            ];

            $arr[$warehouse] = BaseRepository::getArraySqlGet($goods, $sql);
        }
    }

    return $arr;
}

/*
 * 合计运费
 * 购物车显示
 * 订单分单
 * $type
 */

function get_cart_goods_combined_freight($goods, $type = 0, $region = '', $ru_id = 0, $shipping_id = 0)
{
    $arr = [];
    $new_arr = [];

    if ($type == 1) { //购物提交订单页面显示
        foreach ($goods as $key => $row) {
            foreach ($row as $warehouse => $rows) {
                foreach ($rows as $gkey => $grow) {
                    $trow = get_goods_transport($grow['tid']);

                    if ($grow['extension_code'] == 'package_buy' || (isset($grow['is_shipping']) && $grow['is_shipping'] == 0)) {

                        //商品ID + 商家ID + 运费模板 + 商品运费类型
                        @$arr[$key][$warehouse]['goods_transport'] .= $grow['goods_id'] . "|" . $key . "|" . $grow['tid'] . "|" . $grow['freight'] . "|" . $grow['shipping_fee'] . "|" . $grow['goods_number'] . "|" . $grow['goodsweight'] . "|" . $grow['goods_price'] . "-";

                        if ($grow['freight'] && $trow['freight_type'] == 0) {

                            /**
                             * 商品
                             * 运费模板
                             */

                            $weight = 0; //商品总重量
                            $goods_price = 0; //商品总金额
                            $number = 0; //商品总数量
                        } else {
                            $weight = $grow['goodsweight'] * $grow['goods_number']; //商品总重量
                            $goods_price = $grow['goods_price'] * $grow['goods_number']; //商品总金额
                            $number = $grow['goods_number']; //商品总数量
                        }

                        @$arr[$key][$warehouse]['weight'] += $weight;
                        @$arr[$key][$warehouse]['goods_price'] += $goods_price;
                        @$arr[$key][$warehouse]['number'] += $number;
                        @$arr[$key][$warehouse]['ru_id'] = $key; //商家ID
                        @$arr[$key][$warehouse]['warehouse_id'] = $warehouse; //仓库ID
                        @$arr[$key][$warehouse]['warehouse_name'] = RegionWarehouse::where('region_id', $warehouse)->value('region_name'); //仓库名称
                    }
                }
            }
        }

        foreach ($arr as $key => $row) {
            if (!empty($shipping_id)) {
                $shipping_info = get_shipping_code($shipping_id);
                $shipping_code = $shipping_info['shipping_code'];
            } else {
                $seller_shipping = get_seller_shipping_type($key);
                $shipping_code = $seller_shipping['shipping_code']; //配送代码
            }
            foreach ($row as $warehouse => $rows) {
                @$arr[$key][$warehouse]['shipping'] = get_goods_freight($rows, $rows['warehouse_id'], $region, $rows['goods_number'], $shipping_code);
            }
        }

        $new_arr['shipping_fee'] = 0;
        foreach ($arr as $key => $row) {
            foreach ($row as $warehouse => $rows) {
                //自营--自提时--运费清0
                if (isset($rows['shipping_code']) && $rows['shipping_code'] == 'cac') {
                    $rows['shipping']['shipping_fee'] = 0;
                }
                $new_arr['shipping_fee'] += $rows['shipping']['shipping_fee'];
            }
        }

        $arr = ['ru_list' => $arr, 'shipping' => $new_arr];
        return $arr;
    } elseif ($type == 2) { //订单分单
        $arr = get_cart_goods_warehouse_list($goods);

        foreach ($arr as $warehouse => $row) {
            foreach ($row as $gw => $grow) {
                $grow['goodsweight'] = $grow['goodsweight'] ?? 0;

                if ($grow['extension_code'] == 'package_buy' || (isset($grow['is_shipping']) && $grow['is_shipping'] == 0)) {
                    $trow = get_goods_transport($grow['tid']);

                    //商品ID + 商家ID + 运费模板 + 商品运费类型
                    @$new_arr[$warehouse]['goods_transport'] .= $grow['goods_id'] . "|" . $grow['ru_id'] . "|" . $grow['tid'] . "|" . $grow['freight'] . "|" . $grow['shipping_fee'] . "|" . $grow['goods_number'] . "|" . $grow['goodsweight'] . "|" . $grow['goods_price'] . "-";

                    if ($grow['freight'] && isset($trow['freight_type']) && $trow['freight_type'] == 0) {

                        /**
                         * 商品
                         * 运费模板
                         */

                        $weight = 0; //商品总重量
                        $goods_price = 0; //商品总金额
                        $number = 0; //商品总数量
                    } else {
                        $weight = $grow['goodsweight'] * $grow['goods_number']; //商品总重量
                        $goods_price = $grow['goods_price'] * $grow['goods_number']; //商品总金额
                        $number = $grow['goods_number']; //商品总数量
                    }

                    @$new_arr[$warehouse]['weight'] += $weight; //商品总重量
                    @$new_arr[$warehouse]['goods_price'] += $goods_price; //商品总金额
                    @$new_arr[$warehouse]['number'] += $number; //商品总数量
                    @$new_arr[$warehouse]['ru_id'] = $grow['ru_id']; //商家ID
                    @$new_arr[$warehouse]['warehouse_id'] = $warehouse; //仓库ID
                    @$new_arr[$warehouse]['order_id'] = $grow['order_id']; //订单ID
                    @$new_arr[$warehouse]['warehouse_name'] = RegionWarehouse::where('region_id', $warehouse)->value('region_name'); //仓库名称
                }
            }
        }

        $new_arr['shipping_fee'] = 0;
        foreach ($new_arr as $key => $row) {
            $order = OrderInfo::select('country', 'province', 'city', 'district', 'street', 'shipping_id')
                ->where('order_id', $row['order_id']);
            $order = BaseRepository::getToArrayFirst($order);

            $shipping_arr = $order && $order['shipping_id'] ? explode(",", $order['shipping_id']) : [];
            if ($shipping_arr) {
                foreach ($shipping_arr as $kk => $vv) {
                    $ruid_shipping = explode("|", $vv);
                    if ($vv && count($ruid_shipping) > 1 && $ruid_shipping[0] == $ru_id) {
                        $shipping_info = get_shipping_code($ruid_shipping[1]);
                        $shipping_code = $shipping_info['shipping_code'];
                        continue;
                    }
                }
            }

            @$new_arr[$key]['shipping'] = get_goods_freight($row, $row['warehouse_id'], $order, $row['number'], $shipping_code);
            $new_arr['shipping_fee'] += $new_arr[$key]['shipping']['shipping_fee'];
        }
        $arr = $new_arr;
    }

    return $arr;
}

function get_warehouse_cart_goods_info($goods, $type, $region, $shipping_id = 0)
{
    if ($type == 1) {
        $goods = get_cart_goods_ru_list($goods);
    } else {
        $goods = get_cart_goods_warehouse_list($goods);
    }

    //总运费
    $shipping_fee = get_cart_goods_combined_freight($goods, $type, $region, 0, $shipping_id);

    return $shipping_fee;
}

//列出商家运费详细信息
function get_ru_info_list($ru_list)
{
    $arr = [];
    if ($ru_list) {
        foreach ($ru_list as $key => $row) {
            if ($key == 0) {
                $shop_name = SellerShopinfo::where('ru_id', $key)->value('shop_name');
            } else {
                $shop_information = MerchantsShopInformation::select('shoprz_brand_name', 'shop_name_suffix')
                    ->where('user_id', $key);
                $shop_information = BaseRepository::getToArrayFirst($shop_information);

                $shop_name = $shop_information ? $shop_information['shoprz_brand_name'] . $shop_information['shop_name_suffix'] : '';
            }

            $arr[$key]['ru_name'] = $shop_name;
            $arr[$key]['ru_shipping'] = $row;
            foreach ($row as $warehouse => $rows) {
                $arr[$key]['shipping_fee'] += $rows['shipping']['shipping_fee'];
            }

            $arr[$key]['shippingFee'] = $arr[$key]['shipping_fee'];
            $arr[$key]['shipping_fee'] = app(DscRepository::class)->getPriceFormat($arr[$key]['shipping_fee'], false);
        }
    }

    return $arr;
}

//划分商家或平台运费 end

/*
 * 读取缓存文件
 */
function get_cache_site_file($file = '', $var_arr = [])
{
    static $arr = null;
    if ($arr === null) {
        $data = read_static_cache($file);
        if ($data === false) {
            if ($file == 'category_tree' || $file == 'category_tree1' || $file == 'category_tree2') {
                if (empty($var_arr)) {
                    $arr = get_categories_tree_pro();
                } else {
                    $arr = get_categories_tree_pro($var_arr[0], $var_arr[1]);
                }
            } else {
                $arr = $var_arr;
            }

            write_static_cache($file, $arr);
        } else {
            $arr = $data;
        }
    }

    return $arr;
}

//获取当前位置区域
function get_current_region_list($province_id = 1, $region_type = 1)
{
    $region_list = Region::where('parent_id', $province_id)->where('region_type', $region_type);
    $region_list = BaseRepository::getToArrayGet($region_list);

    return $region_list;
}

//讨论圈信息列表
function get_discuss_all_list($goods_id = 0, $dis_type = 0, $reply_page = 1, $size = 40, $revType = 0, $sort = 'add_time', $did = 0)
{
    $page = isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
    $pager = [];
    $arr = [];
    $arr1 = [];
    $dis_action = 1;

    $dis_count = 0;
    if ($dis_type == 4 || $dis_type == 0) {
        //晒单贴
        if ($dis_type == 4) {
            $dis_action = 0;
        }

        $record_count = Comment::where('id_value', $goods_id)->where('status', 1)->where('add_comment_id', 0);

        $record_count = $record_count->whereHasIn('getCommentImg', function ($query) {
            $query->where('comment_img', '<>', '');
        });

        $record_count = $record_count->where('comment_id', '<>', $did)->count();

        if ($dis_type == 0) {
            $dis_count = $record_count;
        }

        $pageType = 1;
        $id = '"' . $goods_id . "|" . $dis_type . "|" . $revType . "|" . $sort . '"';

        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'id' => $id,
            'page' => $reply_page,
            'funName' => 'discuss_list_gotoPage',
            'pageType' => $pageType
        ];
        $discuss = new Pager($pagerParams);
        $pager = $discuss->fpage([0, 4, 5, 6, 9]);

        $res = Comment::where('id_value', $goods_id)->where('status', 1)->where('add_comment_id', 0);

        $res = $res->whereHasIn('getCommentImg', function ($query) {
            $query->where('comment_img', '<>', '');
        });

        $res = $res->where('comment_id', '<>', $did);

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if ($dis_type == 0) {
                    $arr1[$key]['dis_id'] = $row['comment_id'];
                    $arr1[$key]['user_name'] = setAnonymous($row['user_name']); //处理用户名 by wu
                    $arr1[$key]['dis_title'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                    $arr1[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);
                    $arr1[$key]['reply_num'] = DiscussCircle::where('parent_id', $row['comment_id'])->where('review_status', 3)->count();
                    $arr1[$key]['dis_browse_num'] = $row['useful'];
                    $arr1[$key]['dis_type'] = 4;
                } else {
                    $row['user_name'] = setAnonymous($row['user_name']); //处理用户名 by wu
                    $arr[$key] = $row;
                    $arr[$key]['dis_id'] = $row['comment_id'];
                    $arr[$key]['dis_title'] = nl2br(str_replace('\n', '<br />', htmlspecialchars($row['content'])));
                    $arr[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);
                    $arr[$key]['reply_num'] = DiscussCircle::where('parent_id', $row['comment_id'])->where('review_status', 3)->count();
                    $arr[$key]['dis_browse_num'] = $row['useful'];
                    $arr[$key]['dis_type'] = 4;
                }
            }
        }
    }

    $record_count = 0;
    if ($dis_action == 1) {
        $id = '"' . $goods_id . "|" . $dis_type . "|" . $revType . "|" . $sort . '"';

        $record_count = get_discuss_type_count($goods_id, $dis_type, $did);

        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'id' => $id,
            'page' => $reply_page,
            'funName' => 'discuss_list_gotoPage',
            'pageType' => 1
        ];
        $discuss = new Pager($pagerParams);
        $pager = $discuss->fpage([0, 4, 5, 6, 9]);

        if ($sort == 'reply_num') {
            $sort = 'dis_id';
        }

        $res = DiscussCircle::where('parent_id', 0)
            ->where('review_status', 3)
            ->where('goods_id', $goods_id)
            ->where('dis_id', '<>', $did);

        if ($dis_type > 0) {
            $res->where('dis_type', $dis_type);
        }

        $res = $res->with(['getUsers' => function ($query) {
            $query->select('user_id', 'nick_name', 'user_name');
        }
        ]);

        $res = $res->orderBy($sort, 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $users = $row['get_users'] ? $row['get_users'] : [];
                $row['user_name'] = $users ? $users['user_name'] : '';
                $row['nick_name'] = $users ? $users['nick_name'] : '';

                $reply_num = DiscussCircle::where('parent_id', $row['dis_id'])->where('review_status', 3)->count();
                $row['reply_num'] = $reply_num;

                $row['user_name'] = setAnonymous($row['user_name']); //处理用户名 by wu

                $arr[$key] = $row;
                $arr[$key]['add_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['add_time']);
                $arr[$key]['reply_num'] = $row['reply_num'];
            }
        }
    }
    if ($arr1) {
        $arr = array_merge($arr, $arr1);
        $record_count += $dis_count;
    }

    return ['list' => $arr, 'pager' => $pager, 'record_count' => $record_count];
}

//论坛信息数量
function get_discuss_type_count($goods_id, $dis_type = 0, $did = 0)
{
    $res = DiscussCircle::where('parent_id', 0)
        ->where('review_status', 3)
        ->where('goods_id', $goods_id);

    if ($dis_type > 0) {
        $res = $res->where('dis_type', $dis_type);
    }

    if ($did > 0) {
        $res = $res->where('dis_id', '<>', $did);
    }

    return $res->count();
}

/**
 * 晒单贴有图数量
 * @param int $goods_id
 * @return int
 */
function get_commentImg_count($goods_id = 0)
{
    $num = Comment::where('id_value', $goods_id)->where('status', 1)->where('add_comment_id', 0);

    $num = $num->whereHasIn('getCommentImg', function ($query) {
        $query->where('comment_img', '<>', '');
    });

    return $num->count();
}

/**
 * 删除购物车中的商品
 *
 * @param int $rec_id
 * @param string $step
 */
function flow_drop_cart_goods($rec_id = 0, $step = '')
{
    $user_id = session('user_id', 0);
    $session_id = app(SessionRepository::class)->realCartMacIp();

    $row = Cart::where('rec_id', $rec_id);

    if (!empty($user_id)) {
        $row = $row->where('user_id', $user_id);
    } else {
        $row = $row->where('session_id', $session_id);
    }

    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {
        //如果是超值礼包
        if ($row['extension_code'] == 'package_buy') {
            $res = Cart::where('rec_id', $rec_id);
        } //如果是普通商品，同时删除所有赠品及其配件
        elseif ($row['parent_id'] == 0 && $row['is_gift'] == 0) {

            /* 检查购物车中该普通商品的不可单独销售的配件并删除 */
            $goods_id = $row['goods_id'];
            $CartRes = Cart::select('rec_id')
                ->where('parent_id', $goods_id)
                ->where('extension_code', '<>', 'package_buy')
                ->where('group_id', $row['group_id'])
                ->whereHasIn('getGoods', function ($query) {
                    $query->where('is_alone_sale', 0);
                })
                ->whereHasIn('getGroupGoods', function ($query) use ($goods_id) {
                    $query->where('parent_id', $goods_id);
                });

            $CartRes = $CartRes->pluck('rec_id');
            $_del_str = BaseRepository::getToArray($CartRes);
            $_del_str = BaseRepository::getArrayPush($_del_str, $rec_id);

            $where = [
                'rec_id' => $_del_str,
                'parent_id' => $row['goods_id']
            ];
            $res = Cart::where(function ($query) use ($where) {
                $query->whereIn('rec_id', $where['rec_id'])
                    ->orWhere('parent_id', $where['parent_id'])
                    ->orWhere('is_gift', '<>', 0);
            });

            if ($row['group_id']) {
                $res = $res->where('group_id', $row['group_id']);
            }
        } //如果不是普通商品，只删除该商品即可
        else {
            $res = Cart::where('rec_id', $rec_id);
        }

        if (!empty($user_id)) {
            $res = $res->where('user_id', $user_id);
        } else {
            $res = $res->where('session_id', $session_id);
        }

        $res->delete();

        if ($step == 'drop_to_collect') {

            /* 检查是否已经存在于用户的收藏夹 */
            $count = CollectGoods::where('user_id', $user_id)->where('goods_id', $row['goods_id'])->count();

            if ($count < 1) {
                $other = [
                    'user_id' => $user_id,
                    'goods_id' => $row['goods_id'],
                    'add_time' => gmtime()
                ];
                CollectGoods::insert($other);
            }
        }
    }

    app(CartCommonService::class)->flowClearCartAlone();
}

/**
 * 随机生成用户名
 * @param int $user_id 用户编号
 * @return  string  唯一的编号
 */
function generate_user_sn($user_id)
{
    $user_sn = "SC" . str_repeat('0', 6 - strlen($user_id)) . $user_id;
    $user_name = mysql_like_quote($user_id);

    $sn_list = Users::select('user_name')
        ->where('user_name', 'like', '%' . $user_name . '%')
        ->where('user_id', $user_id);
    $sn_list = $sn_list->orderByRaw("LENGTH(user_name) DESC");
    $sn_list = BaseRepository::getToArrayGet($sn_list);
    $sn_list = BaseRepository::getKeyPluck($sn_list, 'user_name');

    if ($sn_list && in_array($user_sn, $sn_list)) {
        $max = pow(10, strlen($sn_list[0]) - strlen($user_sn) + 1) - 1;
        $new_sn = $user_sn . mt_rand(0, $max);
        while (in_array($new_sn, $sn_list)) {
            $new_sn = $user_sn . mt_rand(0, $max);
        }
        $user_sn = $new_sn;
    }

    return $user_sn;
}

/**
 * 获得限时批发的状态
 *
 * @access  public
 * @param array
 * @return  integer
 */
function wholesale_status($wholesale)
{
    $now = gmtime();
    if ($now < $wholesale['start_time']) {
        $status = GBS_PRE_START;
    } elseif ($now > $wholesale['end_time']) {
        $status = GBS_FINISHED;
    } else {
        if ($wholesale['is_finished'] == 0) {
            $status = GBS_UNDER_WAY;
        } else {
            $status = GBS_FINISHED;
        }
    }

    return $status;
}

//查询购买过的商品列表
function get_order_goods_buy_list($warehouse_id = 0, $area_id = 0, $area_city = 0, $num = 18)
{
    $user_id = session('user_id', 0);

    $res = OrderGoods::select('goods_id')->distinct()->whereHasIn("getOrder", function ($query) use ($user_id) {
        $query = $query->where('main_count', 0);
        $query->where('user_id', $user_id);
    });

    $res = $res->whereHasIn('getGoods');

    $where = [
        'warehouse_id' => $warehouse_id,
        'area_id' => $area_id,
        'area_city' => $area_city,
        'area_pricetype' => $GLOBALS['_CFG']['area_pricetype']
    ];

    $user_rank = session('user_rank');
    $res = $res->with([
        'getGoods',
        'getMemberPrice' => function ($query) use ($user_rank) {
            $query->where('user_rank', $user_rank);
        },
        'getWarehouseGoods' => function ($query) use ($where) {
            $query->where('region_id', $where['warehouse_id']);
        },
        'getWarehouseAreaGoods' => function ($query) use ($where) {
            $query = $query->where('region_id', $where['area_id']);

            if ($where['area_pricetype'] == 1) {
                $query->where('city_id', $where['area_city']);
            }
        }
    ]);

    $res = $res->take($num);

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $key => $row) {
            $row = $row['get_goods'] ? array_merge($row, $row['get_goods']) : $row;

            $res[$key] = $row;
        }

        $res = BaseRepository::getSortBy($res, 'sales_volume');

        $seller_id = BaseRepository::getArrayPush($res, 'user_id');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

        foreach ($res as $key => $row) {
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

            $price = app(GoodsCommonService::class)->getGoodsPrice($price, session('discount'), $row);

            $row['shop_price'] = $price['shop_price'];
            $row['promote_price'] = $price['promote_price'];
            $row['goods_number'] = $price['goods_number'];

            $arr[$key] = $row;

            $arr[$key]['goods_id'] = $row['goods_id'];
            $arr[$key]['goods_name'] = $row['goods_name'];

            /* 修正商品图片 */
            $arr[$key]['goods_img'] = app(DscRepository::class)->getImagePath($row['goods_img']);
            $arr[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);

            /* 修正促销价格 */
            if ($row['promote_price'] > 0) {
                $promote_price = app(GoodsCommonService::class)->getBargainPrice($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $arr[$key]['sales_volume'] = $row['sales_volume'];
            $arr[$key]['shop_price'] = app(DscRepository::class)->getPriceFormat($row['shop_price']);
            $arr[$key]['promote_price'] = ($promote_price > 0) ? app(DscRepository::class)->getPriceFormat($promote_price) : '';
            $arr[$key]['url'] = app(DscRepository::class)->buildUri('goods', ['gid' => $row['goods_id']], $row['goods_name']);

            $shop_information = $merchantList[$row['user_id']] ?? []; //通过ru_id获取到店铺信息;
            $arr[$key]['shop_name'] = $shop_information['shop_name'] ?? ''; //店铺名称
            $arr[$key]['country_icon'] = $shop_information['country_icon'] ?? '';

            $build_uri = [
                'urid' => $row['user_id'],
                'append' => $arr[$key]['shop_name'],
            ];

            $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($row['user_id'], $build_uri);
            $arr[$key]['store_url'] = $domain_url['domain_name'];
        }
    }

    return $arr;
}

/**
 * 获取服务器端IP地址
 * @return string
 */
function get_server_ip()
{
    if (request()->server()) {
        $server_addr = request()->server('SERVER_ADDR') ? request()->server('SERVER_ADDR') : '';
        $local_addr = request()->server('LOCAL_ADDR') ? request()->server('LOCAL_ADDR') : '';

        if ($server_addr) {
            $server_ip = $server_addr;
        } else {
            $server_ip = $local_addr;
        }
    } else {
        $server_ip = getenv('SERVER_ADDR');
    }

    return $server_ip;
}

function sc_guid()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

/**
 * 商品详情图片替换
 *
 * @param string $endpoint
 * @param string $text_desc
 * @param string $str_file
 * @return array
 */
function get_goods_desc_images_preg($endpoint = '', $text_desc = '', $str_file = 'goods_desc')
{
    $endpoint = rtrim($endpoint, '/') . '/';

    $url = url('/') . '/';
    $image_dir = app(Shop::class)->image_dir();
    $data_dir = app(Shop::class)->data_dir();

    $pathImage = [
        $url . 'storage/' . $image_dir . '/',
        $url . $image_dir . '/'
    ];

    $pathData = [
        $url . 'storage/' . $data_dir . '/',
        $url . $data_dir . '/'
    ];

    $uploads = $url . 'storage/uploads/';

    if ($text_desc) {
        $text_desc = stripcslashes($text_desc);
        $preg = '/<img.*?src=[\"|\']?(.*?)[\"|\'].*?>/i';
        preg_match_all($preg, $text_desc, $desc_img);
    } else {
        $desc_img = '';
    }

    $arr = [];
    if ($desc_img) {
        $img_list = isset($desc_img[1]) && $desc_img[1] ? array_unique($desc_img[1]) : [];//剔除重复值，防止重复添加域名

        if ($img_list && $endpoint) {
            foreach ($img_list as $key => $row) {
                $row = trim($row);
                if ($GLOBALS['_CFG']['open_oss'] == 1) {
                    if (strpos($row, $url . 'storage/' . $image_dir) !== false || strpos($row, $url . $image_dir) !== false) {
                        $row = str_replace($pathImage, '', $row);
                        $arr[] = 'storage/' . $image_dir . '/' . $row;

                        $text_desc = str_replace($pathImage, $endpoint . $image_dir . '/', $text_desc);
                    } elseif (strpos($row, $url . 'storage/' . $data_dir) !== false || strpos($row, $url . $data_dir) !== false) {
                        $row = str_replace($pathData, '', $row);
                        $arr[] = 'storage/' . $data_dir . '/' . $row;

                        $text_desc = str_replace($pathData, $endpoint . $data_dir . '/', $text_desc);
                    } elseif (strpos($row, $uploads) !== false) {
                        $arr[] = 'storage/uploads/' . $row;
                    }
                } else {
                    if (strpos($row, 'http://') !== false || strpos($row, 'https://') !== false) {
                        if (strpos($row, 'storage/' . $image_dir) !== false || strpos($row, $image_dir) !== false) {
                            $row = str_replace($pathImage, '', $row);
                            $arr[] = 'storage/' . $image_dir . '/' . $row;

                            $text_desc = str_replace($pathImage, asset('/') . 'storage/' . $image_dir . '/', $text_desc);
                        } elseif (strpos($row, 'storage/' . $data_dir) !== false || strpos($row, $data_dir) !== false) {
                            $row = str_replace($pathData, '', $row);
                            $arr[] = 'storage/' . $data_dir . '/' . $row;

                            $text_desc = str_replace($pathData, asset('/') . 'storage/' . $data_dir . '/', $text_desc);
                        } elseif (strpos($row, $uploads) !== false) {
                            if (strpos($row, 'http') !== false) {
                                $arr[] = $row;
                            } else {
                                $arr[] = 'storage/uploads/' . $row;
                            }
                        }
                    } else {
                        if (strpos($row, 'storage') !== false) {
                            $arr[] = $row;
                            $text_desc = str_replace($row, asset('/') . $row, $text_desc);
                        } else {
                            $arr[] = 'storage/' . $row;
                            $text_desc = str_replace($row, asset('/') . 'storage/' . $row, $text_desc);
                        }
                    }
                }
            }
        } else {
            $arr = $img_list;
        }
    }

    $res = ['images_list' => $arr, $str_file => $text_desc];
    return $res;
}

//删除内容图片
function get_desc_images_del($images_list = '')
{
    $image_dir = app(Shop::class)->image_dir();

    if ($images_list) {
        for ($i = 0; $i < count($images_list); $i++) {
            $img = explode($image_dir, $images_list[$i]);

            if (isset($img[1])) {
                dsc_unlink(storage_public($image_dir . $img[1]));
            }
        }
    }
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function G($start, $end = '', $dec = 4)
{
    static $_info = [];
    static $_mem = [];
    if (is_float($end)) { // 记录时间
        $_info[$start] = $end;
    } elseif (!empty($end)) { // 统计时间和内存使用
        if (!isset($_info[$end])) {
            $_info[$end] = microtime(true);
        }
        if ($dec == 'm') {
            if (!isset($_mem[$end])) {
                $_mem[$end] = memory_get_usage();
            }
            return number_format(($_mem[$end] - $_mem[$start]) / 1024);
        } else {
            return number_format(($_info[$end] - $_info[$start]), $dec);
        }
    } else { // 记录时间和内存使用
        $_info[$start] = microtime(true);
        $_mem[$start] = memory_get_usage();
    }
    return null;
}

function unique_arr($arr, $step = 0)
{
    $new = [];
    $u_arr = [];
    foreach ($arr as $k1 => $r1) {
        if (isset($r1['user_id'])) {
            $u_arr[] = $r1;
            array_push($new, $r1);
        }
    }

    if ($u_arr) {
        foreach ($u_arr as $k3 => $r3) {
            foreach ($arr as $k2 => $r2) {
                if ($r2['brand_id'] == $r3['brand_id']) {
                    unset($arr[$k2]);
                }
            }
        }
    }

    foreach ($arr as $r1) {
        $new[] = $r1;
    }

    if ($step > 0) {
        $new = array_slice($new, 0, $step);
    }

    return $new;
}

//查询系统配置文件code值
function get_shop_config_val($val = '')
{
    $sel_config = [];

    if (defined('CACHE_MEMCACHED')) {
        $sel_config['open_memcached'] = CACHE_MEMCACHED;
    } else {
        $sel_config['open_memcached'] = 0;
    }

    return $sel_config;
}

/*
 * 店铺分类列表
 */

function get_category_store_list($ru_id = 0, $is_url = 0, $level = 0, $parent_id = 0)
{
    $filter['ru_id'] = isset($_REQUEST['ru_id']) && !empty($_REQUEST['ru_id']) ? intval($_REQUEST['ru_id']) : $ru_id;
    $filter['is_url'] = isset($_REQUEST['is_url']) && !empty($_REQUEST['is_url']) ? intval($_REQUEST['is_url']) : $is_url;
    $filter['level'] = isset($_REQUEST['level']) && !empty($_REQUEST['level']) ? intval($_REQUEST['level']) : $level;
    $filter['parent_id'] = isset($_REQUEST['parent_id']) && !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : $parent_id;
    $filter['merchant_id'] = isset($_REQUEST['merchant_id']) && !empty($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;

    /* 记录总数 */
    $record_count = MerchantsCategory::where('parent_id', $filter['parent_id']);

    if ($filter['ru_id'] || $filter['merchant_id']) {
        if ($filter['merchant_id']) {
            $record_count = $record_count->where('user_id', $filter['merchant_id']);
        }

        if ($filter['ru_id']) {
            $record_count = $record_count->where('user_id', $filter['ru_id']);
        }
    } else {
        $record_count = $record_count->where('user_id', '<>', 0);
    }

    $filter['record_count'] = $record_count->count();

    /* 分页大小 */
    $filter = page_and_size($filter);

    $res = MerchantsCategory::where('parent_id', $filter['parent_id']);

    if ($filter['ru_id'] || $filter['merchant_id']) {
        if ($filter['merchant_id']) {
            $res = $res->where('user_id', $filter['merchant_id']);
        }

        if ($filter['ru_id']) {
            $res = $res->where('user_id', $filter['ru_id']);
        }
    } else {
        $res = $res->where('user_id', '<>', 0);
    }

    $res = $res->orderByRaw('cat_id, sort_order asc');

    if ($filter['start'] > 0) {
        $res = $res->skip($filter['start']);
    }

    if ($filter['page_size'] > 0) {
        $res = $res->take($filter['page_size']);
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {

        $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

        $goodsList = GoodsDataHandleService::getSellerGoodsDataList($ru_id);

        foreach ($res as $key => $row) {

            //查询服分类下子分类下的商品数量 start
            $cat_id_str = get_class_nav($row['cat_id'], 'merchants_category');
            $row['cat_child'] = $cat_id_str ? substr($cat_id_str['catId'], 0, -1) : '';

            if (empty($row['cat_child'])) {
                $row['cat_child'] = substr($row['cat_id'], 0, -1);
            }

            $cat_child = [];
            if ($row['cat_child'] && !is_array($row['cat_child'])) {
                $cat_child = explode(",", $row['cat_child']);
            }

            $goodsNums = $goodsList[$row['user_id']] ?? [];
            $sql = [
                'where' => [
                    [
                        'name' => 'is_delete',
                        'value' => 0
                    ]
                ]
            ];

            if ($cat_child) {
                $sql['whereIn'][] = [
                    'name' => 'user_cat',
                    'value' => $cat_child
                ];
            }

            $goodsNums = BaseRepository::getArraySqlGet($goodsNums, $sql);
            $row['goodsNum'] = $row['goods_num'] = count($goodsNums);

            $row['goodsCat'] = 0; //扩展商品数量
            //查询服分类下子分类下的商品数量 end

            $row['user_name'] = $merchantList[$row['user_id']]['shop_name'] ?? '';
            $row['level'] = $filter['level'];

            if ($is_url) {
                $build_uri = [
                    'urid' => $row['user_id'],
                    'append' => $row['user_name'],
                    'cid' => $row['cat_id']
                ];

                $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($row['user_id'], $build_uri);
                $row['url'] = $domain_url['domain_name'];
            }

            $arr[$key] = $row;
        }
    }

    return ['cate' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
}

/*
 * 根据某一特定键(下标)取出一维或多维数组的所有值；不用循环的理由是考虑大数组的效率，把数组序列化，然后根据序列化结构的特点提取需要的字符串
 */

function array_get_by_key(array $array, $string)
{
    if (!trim($string)) {
        return false;
    }
    preg_match_all("/\"$string\";\w{1}:(?:\d+:|)(.*?);/", serialize($array), $res);
    return $res[1];
}

//数组读取
function get_store_cat_read($cat_list, $level = 0)
{
    $arr = [];

    if ($cat_list) {
        foreach ($cat_list as $key => $row) {
            if ($row['level'] == $level) {
                $row['level'] = $level - 3;
                $row['level_type'] = $level;
                $arr[$key] = $row;
            }
        }

        $arr = array_values($arr);
    }

    return $arr;
}

function get_shipping_code($shipping_id = 0)
{
    $row = Shipping::where('shipping_id', $shipping_id);
    $row = BaseRepository::getToArrayFirst($row);

    return $row;
}

/**
 * 实名认证信息
 */
function get_users_real($user_id = 0, $user_type = 0)
{
    $res = UsersReal::where('user_id', $user_id)
        ->where('user_type', $user_type);

    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

//获取投诉类型
function get_goods_report_type()
{
    $report_type = GoodsReportType::where('is_show', 1);
    $report_type = BaseRepository::getToArrayGet($report_type);

    return $report_type;
}

//获取投诉主题
function get_goods_report_title($type_id = 0)
{
    $report_title = GoodsReportTitle::where('is_show', 1);

    if ($type_id > 0) {
        $report_title = $report_title->where('type_id', $type_id);
    }

    $report_title = BaseRepository::getToArrayGet($report_title);

    if ($report_title) {
        foreach ($report_title as $k => $v) {
            if ($v && $v['type_id'] > 0) {
                $report_title[$k]['type_name'] = GoodsReportType::where('type_id', $v['type_id'])->value('type_name');
            }
        }
    }

    return $report_title;
}

function get_complaint_title()
{
    $res = ComplainTitle::where('is_show', 1);
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

//获取交易纠纷详情
function get_complaint_info($complaint_id = 0)
{
    //获取投诉详情
    $complaint_info = Complaint::where('complaint_id', $complaint_id);
    $complaint_info = BaseRepository::getToArrayFirst($complaint_info);

    if (empty($complaint_info)) {
        return [];
    }

    $title_name = ComplainTitle::where('title_id', $complaint_info['title_id'])->value('title_name');
    $complaint_info['title_name'] = $title_name;

    //获取举报图片列表
    $img_list = ComplaintImg::where('complaint_id', $complaint_info['complaint_id'])
        ->orderBy('img_id', 'desc');
    $img_list = BaseRepository::getToArrayGet($img_list);

    if (!empty($img_list)) {
        foreach ($img_list as $k => $v) {
            $img_list[$k]['img_file'] = app(DscRepository::class)->getImagePath($v['img_file']);
        }
    }
    $complaint_info['img_list'] = $img_list;

    //申诉图片列表
    $appeal_img = AppealImg::where('complaint_id', $complaint_info['complaint_id'])
        ->orderBy('img_id', 'desc');
    $appeal_img = BaseRepository::getToArrayGet($appeal_img);

    if (!empty($appeal_img)) {
        foreach ($appeal_img as $k => $v) {
            $appeal_img[$k]['img_file'] = app(DscRepository::class)->getImagePath($v['img_file']);
        }
    }
    $complaint_info['appeal_img'] = $appeal_img;

    //获取操作人
    $complaint_info['end_handle_user'] = AdminUser::where('user_id', $complaint_info['end_admin_id'])->value('user_name');
    $complaint_info['handle_user'] = AdminUser::where('user_id', $complaint_info['admin_id'])->value('user_name');

    $complaint_info['add_time'] = TimeRepository::getLocalDate('Y - m - d H:i:s', $complaint_info['add_time']);
    $complaint_info['appeal_time'] = TimeRepository::getLocalDate('Y - m - d H:i:s', $complaint_info['appeal_time']);
    $complaint_info['end_handle_time'] = TimeRepository::getLocalDate('Y - m - d H:i:s', $complaint_info['end_handle_time']);
    $complaint_info['complaint_handle_time'] = TimeRepository::getLocalDate('Y - m - d H:i:s', $complaint_info['complaint_handle_time']);

    return $complaint_info;
}

//获取谈话
//$type查看聊天人类型   0平台，1商家，2会员
function checkTalkView($complaint_id = 0, $type = 'admin')
{
    $talk_list = ComplaintTalk::where('complaint_id', $complaint_id)->orderBy('talk_time');
    $talk_list = BaseRepository::getToArrayGet($talk_list);

    if ($talk_list) {
        foreach ($talk_list as $k => $v) {
            $talk_list[$k]['talk_time'] = TimeRepository::getLocalDate('Y - m - d H:i:s', $v['talk_time']);
            if ($v['view_state']) {
                $view_state = explode(',', $v['view_state']);
                if (!in_array($type, $view_state)) {
                    $view_state_new = $v['view_state'] . "," . $type;

                    ComplaintTalk::where('talk_id', $v['talk_id'])->update(['view_state' => $view_state_new]);
                }
            }
        }
    }

    return $talk_list;
}

//删除举报相关图片
function del_complaint_img($complaint_id = 0, $table = 'complaint_img')
{
    $img_list = ComplaintImg::where('complaint_id', $complaint_id)->orderBy('img_id', 'desc');
    $img_list = BaseRepository::getToArrayGet($img_list);

    if (!empty($img_list)) {
        foreach ($img_list as $k => $v) {
            if ($v['img_file']) {
                if ($table == 'appeal_img') {
                    AppealImg::where('img_id', $v['img_id'])->delete();
                } else {
                    ComplaintImg::where('img_id', $v['img_id'])->delete();
                }

                app(DscRepository::class)->getOssDelFile([$v['img_file']]);
                addslashes(storage_public($v['img_file']));
            }
        }
    }
    return false;
}

//删除谈话
function del_complaint_talk($complaint_id = 0)
{
    return ComplaintTalk::where('complaint_id', $complaint_id)->delete();
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param int $cat_id 分类的ID
 * @param int $selected 当前选中分类的ID
 * @param boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level 限定返回的级数。为0时返回所有级数
 * @param int $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function get_goods_lib_cat($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
    static $res = null;

    if ($res === null) {
        $data = read_static_cache('goods_lib_cat_releate');
        if ($data === false) {
            $res = GoodsLibCat::whereRaw(1)
                ->orderByRaw("parent_id, sort_order asc");
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $has_children = GoodsLibCat::where('parent_id', $row['cat_id']);
                    $has_children = BaseRepository::getToArrayGet($has_children);

                    $res[$key]['has_children'] = $has_children;
                }
            }

            //如果数组过大，不采用静态缓存方式
            if (count($res) <= 1000) {
                write_static_cache('goods_lib_cat_releate', $res);
            }
        } else {
            $res = $data;
        }
    }

    if (empty($res) == true) {
        return $re_type ? '' : [];
    }

    $options = goods_lib_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组
    $children_level = 99999; //大于这个分类的将被删除
    if ($is_show_all == false) {
        foreach ($options as $key => $val) {
            if ($val['level'] > $children_level) {
                unset($options[$key]);
            } else {
                if ($val['is_show'] == 0) {
                    unset($options[$key]);
                    if ($children_level > $val['level']) {
                        $children_level = $val['level']; //标记一下，这样子分类也能删除
                    }
                } else {
                    $children_level = 99999; //恢复初始值
                }
            }
        }
    }

    /* 截取到指定的缩减级别 */
    if ($level > 0) {
        if ($cat_id == 0) {
            $end_level = $level;
        } else {
            $first_item = reset($options); // 获取第一个元素
            $end_level = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options as $key => $val) {
            if ($val['level'] >= $end_level) {
                unset($options[$key]);
            }
        }
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= ' < option value = "' . $var['cat_id'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= ' > ';
            if ($var['level'] > 0) {
                $select .= str_repeat(' &nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . ' </option > ';
        }
        return $select;
    } else {
        foreach ($options as $key => $value) {
            if ($value['level'] > 0) {
                $options[$key]['name'] = str_repeat(' &nbsp;', $value['level'] * 4) . $value['cat_name'];
            }
        }
        return $options;
    }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param int $cat_id 上级分类ID
 * @param array $arr 含有所有分类的数组
 * @param int $level 级别
 * @return  void
 */
function goods_lib_cat_options($spec_cat_id, $arr)
{
    static $cat_options = [];

    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = [];
        $data = read_static_cache('goods_lib_cat_option_static');
        if ($data === false) {
            while (!empty($arr)) {
                foreach ($arr as $key => $value) {
                    $cat_id = $value['cat_id'];
                    if ($level == 0 && $last_cat_id == 0) {
                        if ($value['parent_id'] > 0) {
                            break;
                        }

                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['cat_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] == 0) {
                            continue;
                        }
                        $last_cat_id = $cat_id;
                        $cat_id_array = [$cat_id];
                        $level_array[$last_cat_id] = ++$level;
                        continue;
                    }

                    if ($value['parent_id'] == $last_cat_id) {
                        $options[$cat_id] = $value;
                        $options[$cat_id]['level'] = $level;
                        $options[$cat_id]['id'] = $cat_id;
                        $options[$cat_id]['name'] = $value['cat_name'];
                        unset($arr[$key]);

                        if ($value['has_children'] > 0) {
                            if (end($cat_id_array) != $last_cat_id) {
                                $cat_id_array[] = $last_cat_id;
                            }
                            $last_cat_id = $cat_id;
                            $cat_id_array[] = $cat_id;
                            $level_array[$last_cat_id] = ++$level;
                        }
                    } elseif ($value['parent_id'] > $last_cat_id) {
                        break;
                    }
                }

                $count = count($cat_id_array);
                if ($count > 1) {
                    $last_cat_id = array_pop($cat_id_array);
                } elseif ($count == 1) {
                    if ($last_cat_id != end($cat_id_array)) {
                        $last_cat_id = end($cat_id_array);
                    } else {
                        $level = 0;
                        $last_cat_id = 0;
                        $cat_id_array = [];
                        continue;
                    }
                }

                if ($last_cat_id && isset($level_array[$last_cat_id])) {
                    $level = $level_array[$last_cat_id];
                } else {
                    $level = 0;
                }
            }
            //如果数组过大，不采用静态缓存方式
            if (count($options) <= 2000) {
                write_static_cache('goods_lib_cat_option_static', $options);
            }
        } else {
            $options = $data;
        }
        $cat_options[0] = $options;
    } else {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id) {
        return $options;
    } else {
        if (empty($options[$spec_cat_id])) {
            return [];
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options as $key => $value) {
            if ($key != $spec_cat_id) {
                unset($options[$key]);
            } else {
                break;
            }
        }

        $spec_cat_id_array = [];
        foreach ($options as $key => $value) {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) || ($spec_cat_id_level > $value['level'])) {
                break;
            } else {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

/**
 * 品牌信息
 */
function get_brand_url($brand_id = 0)
{
    $res = Brand::where('brand_id', $brand_id);
    $res = BaseRepository::getToArrayFirst($res);

    if ($res) {
        $res['url'] = app(DscRepository::class)->buildUri('brand', ['bid' => $res['brand_id']], $res['brand_name']);
        $res['brand_logo'] = empty($res['brand_logo']) ? str_replace([' ../'], '', $GLOBALS['_CFG']['no_brand']) : 'data/brandlogo/' . $res['brand_logo'];
        $res['brand_logo'] = app(DscRepository::class)->getImagePath($res['brand_logo']);
    }

    return $res;
}

/**
 * 取得配送方式信息
 * @param int $shipping 配送方式id
 * @param array $select
 * @return  array   配送方式信息
 */
function shipping_info($shipping, $select = [])
{
    return \App\Repositories\Flow\FlowRepository::shipping_info($shipping, $select);
}

//获取商品的智能权重数据
function get_manual_intervention($goods_id = 0)
{
    $res = IntelligentWeight::where('goods_id', $goods_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}
