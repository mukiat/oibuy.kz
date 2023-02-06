<?php

use App\Libraries\Pinyin;
use App\Models\AccountLog;
use App\Models\AdminLog;
use App\Models\AdminUser;
use App\Models\ArticleCat;
use App\Models\BargainGoods;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Complaint;
use App\Models\FavourableActivity;
use App\Models\Goods;
use App\Models\GoodsActivity;
use App\Models\GoodsCat;
use App\Models\GoodsExtend;
use App\Models\GoodsLibCat;
use App\Models\GoodsTransport;
use App\Models\GoodsTransportExpress;
use App\Models\GoodsTransportExtend;
use App\Models\GoodsTransportTpl;
use App\Models\IntelligentWeight;
use App\Models\LinkBrand;
use App\Models\MailTemplates;
use App\Models\MerchantsAccountLog;
use App\Models\MerchantsCategory;
use App\Models\MerchantsGrade;
use App\Models\MerchantsShopBrand;
use App\Models\MerchantsShopInformation;
use App\Models\OfflineStore;
use App\Models\OrderAction;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PresaleActivity;
use App\Models\PresaleCat;
use App\Models\Products;
use App\Models\Region;
use App\Models\RegionStore;
use App\Models\ReturnAction;
use App\Models\ReturnCause;
use App\Models\RsRegion;
use App\Models\SeckillGoods;
use App\Models\SellerApplyInfo;
use App\Models\SellerGrade;
use App\Models\SellerShopinfo;
use App\Models\SellerTemplateApply;
use App\Models\Seo;
use App\Models\Shipping;
use App\Models\ShippingArea;
use App\Models\Single;
use App\Models\SnatchLog;
use App\Models\Stages;
use App\Models\StoreOrder;
use App\Models\TeamGoods;
use App\Models\TradeSnapshot;
use App\Models\Users;
use App\Models\ValueCardRecord;
use App\Models\VirtualCard;
use App\Models\ZcCategory;
use App\Models\ZcGoods;
use App\Models\ZcProject;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Erp\JigonManageService;
use App\Services\Goods\GoodsAttrService;
use App\Services\Goods\GoodsCommonService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Goods\GoodsGalleryService;
use App\Services\Goods\GoodsProdutsService;
use App\Services\Goods\GoodsWarehouseService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderDataHandleService;
use App\Services\User\UserRankService;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSmsChangUserMoney;

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param mix $item_list 列表数组或字符串
 * @param string $field_name 字段名称
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '', $not = '')
{
    if (!empty($not)) {
        $not = " " . $not;
    }

    if (empty($item_list)) {
        return $field_name . $not . " IN ('') ";
    } else {
        if (!is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list as $item) {
            if ($item !== '') {
                $item = addslashes($item);
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . $not . " IN ('') ";
        } else {
            $item_list_tmp = app(DscRepository::class)->delStrComma($item_list_tmp);
            return $field_name . $not . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 获得指定国家的所有省份
 *
 * @param int $type
 * @param int $parent
 * @return mixed
 */
function get_regions($type = 0, $parent = 0)
{
    $res = Region::where('region_type', $type)->where('parent_id', $parent);
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

/**
 * 获得配送区域中指定的配送方式的配送费用的计算参数
 *
 * @access  public
 * @param int $area_id 配送区域ID
 *
 * @return array;
 */
function get_shipping_config($area_id)
{
    /* 获得配置信息 */
    $cfg = ShippingArea::where('shipping_area_id', $area_id)->value('configure');

    if ($cfg) {
        /* 拆分成配置信息的数组 */
        $arr = unserialize($cfg);
    } else {
        $arr = [];
    }

    return $arr;
}

/**
 * 初始化会员数据整合类
 *
 * @access  public
 * @return  object
 */
function init_users()
{
    static $cls = null;
    if ($cls != null) {
        return $cls;
    }

    $integrate_code = config('shop.integrate_code', 'Passport');
    $integrate_code = ($integrate_code == 'dscmall') ? 'Passport' : $integrate_code;

    $plugin = "\\App\\Plugins\\Integrates\\" . StrRepository::studly($integrate_code) . "\\" . StrRepository::studly($integrate_code);

    if (class_exists($plugin)) {
        $integrate_config = config('shop.integrate_config', []);
        $cfg = !empty($integrate_config) ? unserialize($integrate_config) : [];

        $cls = new $plugin($cfg);
    }

    return $cls;
}

//循环加载 start
function flush_echo($data)
{
    ob_end_flush();
    ob_implicit_flush(true);
    echo $data;
}

function show_js_message($message, $ext = 0)
{
    flush_echo('<script type="text/javascript">showmessage(\'' . addslashes($message) . '\',' . $ext . ');</script>' . "\r\n");
}

function sc_stime()
{
    return TimeRepository::getGmTime() + microtime();
}

function sc_timer($stime)
{
    $etime = TimeRepository::getGmTime() + microtime();
    $pass_time = sprintf("%.2f", $etime - $stime);

    //消耗时间
    return $pass_time;
}

//循环加载 end

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param int $cat_id 上级分类ID
 * @param array $arr 含有所有分类的数组
 * @param int $level 级别
 * @return array|mixed
 * @throws Exception
 */
function cat_options($spec_cat_id, $arr)
{
    static $cat_options = [];

    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = [];
        $data = read_static_cache('cat_option_static');
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
                write_static_cache('cat_option_static', $options);
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
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level'])
            ) {
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
 * 取得品牌列表
 * @return array 品牌列表 id => name
 */
function get_brand_list($goods_id = 0, $type = 0, $ru_id = 0)
{
    if ($goods_id > 0) {
        $seller_id = Goods::where('goods_id', $goods_id)->value('user_id');
    } else {
        if ($ru_id > 0) {
            $seller_id = $ru_id;
        } else {
            $adminru = get_admin_ru_id();
            $seller_id = $adminru ? $adminru['ru_id'] : 0;
        }
    }

    if ($type == 2) {
        $brand_list = Brand::whereRaw(1)->count();
    } else {
        $res = Brand::whereRaw(1)->orderBy('sort_order');
        $res = BaseRepository::getToArrayGet($res);

        $brand_list = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if ($seller_id) {
                    $val['is_brand'] = get_seller_brand_count($row['brand_id'], $seller_id);
                } else {
                    $val['is_brand'] = 1;
                }

                if ($val['is_brand'] > 0) {
                    if ($type == 1) {
                        $brand_list[$key]['brand_id'] = $row['brand_id'];
                        $brand_list[$key]['brand_name'] = addslashes($row['brand_name']);
                        $brand_list[$key]['brand_letter'] = $row['brand_letter'];
                        $brand_list[$key]['brand_first_char'] = $row['brand_first_char'];
                    } else {
                        $brand_list[$key]['brand_id'] = $row['brand_id'];
                        $brand_list[$key]['brand_name'] = addslashes($row['brand_name']);
                        $brand_list[$key]['brand_letter'] = $row['brand_letter'];
                    }
                } else {
                    unset($brand_list[$row['brand_id']]);
                }
            }
        }
    }

    if ($brand_list && is_array($brand_list)) {
        $brand_list = array_values($brand_list);
    }

    return $brand_list;
}

/**
 * 取得商家品牌列表
 * @return array 品牌列表 id => name
 */
function get_store_brand_list()
{
    $res = MerchantsShopBrand::where('audit_status');
    $res = CommonRepository::constantMaxId($res, 'user_id');
    $res = $res->orderBy('bid');
    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $key => $row) {
            $res[$row['bid']] = $row;

            $res[$row['bid']] = addslashes($row['brandName']);
        }
    }

    return $res;
}

//by wang 楼层品牌
function get_floor_brand($brand_ids)
{
    $row = [];

    if ($brand_ids && is_array($brand_ids)) {
        $row = Brand::whereIn('brand_id', $brand_ids);
        $row = BaseRepository::getToArrayGet($row);

        if ($row) {
            foreach ($row as $key => $val) {
                $row[$key]['url'] = app(DscRepository::class)->buildUri('brandn', ['bid' => $val['brand_id']], $val['brand_name']);
                $row[$key]['brand_desc'] = htmlspecialchars($val['brand_desc'], ENT_QUOTES);
                $row[$key]['brand_logo'] = app(DscRepository::class)->getImagePath(DATA_DIR . '/brandlogo/' . $val['brand_logo']);
            }
        }
    }
    return $row;
}

/**
 * 判断商品是否有促销活动
 *
 * @param $goods_id
 * @return array
 */
function is_promotion($goods_id)
{
    $arr = [];
    if (!empty($goods_id)) {
        $snatch = [];
        $group = [];
        $auction = [];
        $package = [];
        $seckill = [];
        $presale = [];
        $team = [];
        $bargain = [];

        $goods_id = BaseRepository::getExplode($goods_id);
        $res = GoodsActivity::whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
            $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goodsId
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            foreach ($res as $key => $data) {

                $goods = $goodsList[$data['goods_id']] ?? [];

                switch ($data['act_type']) {
                    case GAT_SNATCH: //夺宝奇兵

                        $snatch['snatch']['type'] = 'snatch';
                        if (isset($snatch['snatch'][$snatch['snatch']['type']]['goods_sn'])) {
                            $snatch['snatch'][$snatch['snatch']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                        } else {
                            $snatch['snatch'][$snatch['snatch']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                        }
                        break;

                    case GAT_GROUP_BUY: //团购

                        $group['group_buy']['type'] = 'group_buy';
                        if (isset($group['group_buy'][$group['group_buy']['type']]['goods_sn'])) {
                            $group['group_buy'][$group['group_buy']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                        } else {
                            $group['group_buy'][$group['group_buy']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                        }
                        break;

                    case GAT_AUCTION: //拍卖

                        $auction['auction']['type'] = 'auction';
                        if (isset($auction['auction'][$auction['auction']['type']]['goods_sn'])) {
                            $auction['auction'][$auction['auction']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                        } else {
                            $auction['auction'][$auction['auction']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                        }
                        break;
                }
            }
        }

        $res = GoodsActivity::whereHasIn('getPackageGoods', function ($query) use ($goods_id) {
            $query->whereIn('goods_id', $goods_id);
        });
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
            $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goodsId
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if ($res) {
                foreach ($res as $data) {

                    $goods = $goodsList[$data['goods_id']] ?? [];

                    switch ($data['act_type']) {
                        case GAT_PACKAGE: //礼包

                            $package['package']['type'] = 'package';
                            if (isset($package['package'][$package['package']['type']]['goods_sn'])) {
                                $package['package'][$package['package']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                            } else {
                                $package['package'][$package['package']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                            }
                            break;
                    }
                }
            }
        }

        // 秒杀
        $res = SeckillGoods::whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);
        if ($res) {

            $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
            $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goodsId
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if ($res) {
                foreach ($res as $key => $val) {

                    $goods = $goodsList[$val['goods_id']] ?? [];

                    $seckill['seckill']['type'] = 'seckill';
                    if (isset($seckill['seckill'][$seckill['seckill']['type']]['goods_sn'])) {
                        $seckill['seckill'][$seckill['seckill']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                    } else {
                        $seckill['seckill'][$seckill['seckill']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                    }
                }
            }
        }

        // 预售 $presale
        $res = PresaleActivity::whereIn('goods_id', $goods_id);
        $res = BaseRepository::getToArrayGet($res);
        if ($res) {

            $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
            $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

            $sql = [
                'whereIn' => [
                    [
                        'name' => 'goods_id',
                        'value' => $goodsId
                    ]
                ]
            ];
            $res = BaseRepository::getArraySqlGet($res, $sql);

            if ($res) {
                foreach ($res as $key => $val) {

                    $goods = $goodsList[$val['goods_id']] ?? [];

                    $presale['presale']['type'] = 'presale';
                    if (isset($presale['presale'][$presale['presale']['type']]['goods_sn'])) {
                        $presale['presale'][$presale['presale']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                    } else {
                        $presale['presale'][$presale['presale']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                    }
                }
            }
        }

        // 拼团活动
        if (file_exists(MOBILE_TEAM)) {
            $res = TeamGoods::whereIn('goods_id', $goods_id)->where('is_team', 1);
            $res = BaseRepository::getToArrayGet($res);
            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $key => $val) {

                        $goods = $goodsList[$val['goods_id']] ?? [];

                        $team['team']['type'] = 'team';
                        if (isset($team['team'][$team['team']['type']]['goods_sn'])) {
                            $team['team'][$team['team']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                        } else {
                            $team['team'][$team['team']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                        }
                    }
                }
            }
        }

        // 砍价
        if (file_exists(MOBILE_BARGAIN)) {
            $res = BargainGoods::whereIn('goods_id', $goods_id)->where('status', 0)->where('is_delete', 0);
            $res = BaseRepository::getToArrayGet($res);
            if ($res) {

                $goodsId = BaseRepository::getKeyPluck($res, 'goods_id');
                $goodsList = GoodsDataHandleService::GoodsDataList($goodsId, ['goods_id', 'goods_sn']);
                $goodsId = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'goods_id',
                            'value' => $goodsId
                        ]
                    ]
                ];
                $res = BaseRepository::getArraySqlGet($res, $sql);

                if ($res) {
                    foreach ($res as $key => $val) {

                        $goods = $goodsList[$val['goods_id']] ?? [];

                        $bargain['bargain']['type'] = 'bargain';
                        if (isset($bargain['bargain'][$bargain['bargain']['type']]['goods_sn'])) {
                            $bargain['bargain'][$bargain['bargain']['type']]['goods_sn'] .= $goods['goods_sn'] . ",";
                        } else {
                            $bargain['bargain'][$bargain['bargain']['type']]['goods_sn'] = $goods['goods_sn'] . ",";
                        }
                    }
                }
            }
        }

        $arr = array_merge($snatch, $group, $auction, $package, $seckill, $presale, $team, $bargain);

    }

    return $arr;
}

/*
 * 验证返回提示
*/
function is_promotion_error($is_promotion = [])
{
    foreach ($is_promotion as $res) {
        $res[$res['type']]['goods_sn'] = isset($res[$res['type']]['goods_sn']) ? app(DscRepository::class)->delStrComma($res[$res['type']]['goods_sn']) : '';

        switch ($res['type']) {
            case 'snatch': //夺宝奇兵
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_snatch'), 0);
                break;

            case 'group_buy': //团购
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_group_buy'), 0);
                break;

            case 'auction': //拍卖
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_auction'), 0);
                break;

            case 'package': //礼包
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_package'), 0);
                break;

            case 'seckill': //秒杀
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_seckill'), 0);
                break;

            case 'presale': //预售
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_presale'), 0);
                break;

            case 'team': //拼团
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_team'), 0);
                break;

            case 'bargain': //砍价
                return make_json_error(__('admin::goods.edit_goods_info') . __('admin::goods.del_bargain'), 0);
                break;
        }
    }

}


/*
 *判断是否是秒杀商品
*/
function is_seckill($goods_id)
{
    $goods_sn = '';
    if (!empty($goods_id)) {
        $goods_id = !is_array($goods_id) ? explode(",", $goods_id) : $goods_id;

        $res = SeckillGoods::whereIn('goods_id', $goods_id);
        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_sn');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {
                $row['goods_sn'] = $row['get_goods'] ? $row['get_goods']['goods_sn'] : '';

                $res[$key] = $row;
            }

            $res = collect($res)->pluck('goods_sn')->all();
        }

        if ($res) {
            $res = !empty($res) ? array_unique($res) : '';
            $goods_sn = !empty($res) ? implode(",", $res) : '';
        }
    }

    return $goods_sn;
}

/*
 *判断是否是预售商品
*/
function is_presale($goods_id)
{
    $goods_sn = '';
    if (!empty($goods_id)) {
        $goods_id = !is_array($goods_id) ? explode(",", $goods_id) : $goods_id;

        $res = PresaleActivity::whereIn('goods_id', $goods_id);
        $res = $res->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_sn');
            }
        ]);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {
                $row['goods_sn'] = $row['get_goods'] ? $row['get_goods']['goods_sn'] : '';

                $res[$key] = $row;
            }

            $res = collect($res)->pluck('goods_sn')->all();
        }

        if ($res) {
            $res = !empty($res) ? array_unique($res) : '';
            $goods_sn = !empty($res) ? implode(",", $res) : '';
        }
    }

    return $goods_sn;
}


/**
 * 所有的促销活动信息
 *
 * @param string $goods_id
 * @param int $ru_id
 * @return array
 * @throws Exception
 */
function get_promotion_info($goods_id = '', $ru_id = 0)
{
    $CategoryRep = app(CategoryService::class);

    $snatch = [];
    $group = [];
    $auction = [];
    $package = [];
    $favourable = [];
    $list_array = [];

    $gmtime = TimeRepository::getGmTime();
    $res = GoodsActivity::where('review_status', 3)
        ->where('is_finished', 0)
        ->where('start_time', '<=', $gmtime)
        ->where('end_time', '>=', $gmtime)
        ->where('user_id', $ru_id);

    if (!empty($goods_id)) {
        $res = $res->where('goods_id', $goods_id);
    }

    $res = $res->take(15);

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $data) {
            switch ($data['act_type']) {
                case GAT_SNATCH: //夺宝奇兵
                    $snatch[$data['act_id']]['act_name'] = $data['act_name'];
                    $snatch[$data['act_id']]['url'] = app(DscRepository::class)->buildUri('snatch', ['sid' => $data['act_id']]);
                    $snatch[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $snatch[$data['act_id']]['sort'] = $data['start_time'];
                    $snatch[$data['act_id']]['type'] = 'snatch';
                    break;

                case GAT_GROUP_BUY: //团购
                    $group[$data['act_id']]['act_name'] = $data['act_name'];
                    $group[$data['act_id']]['url'] = app(DscRepository::class)->buildUri('group_buy', ['gbid' => $data['act_id']]);
                    $group[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $group[$data['act_id']]['sort'] = $data['start_time'];
                    $group[$data['act_id']]['type'] = 'group_buy';
                    break;

                case GAT_AUCTION: //拍卖
                    $auction[$data['act_id']]['act_name'] = $data['act_name'];
                    $auction[$data['act_id']]['url'] = app(DscRepository::class)->buildUri('auction', ['auid' => $data['act_id']]);
                    $auction[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $auction[$data['act_id']]['sort'] = $data['start_time'];
                    $auction[$data['act_id']]['type'] = 'auction';
                    break;

                case GAT_PACKAGE: //礼包
                    $package[$data['act_id']]['act_name'] = $data['act_name'];
                    $package[$data['act_id']]['url'] = 'package.php#' . $data['act_id'];
                    $package[$data['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $data['start_time']), TimeRepository::getLocalDate('Y-m-d', $data['end_time']));
                    $package[$data['act_id']]['sort'] = $data['start_time'];
                    $package[$data['act_id']]['type'] = 'package';
                    break;
            }
        }
    }

    $user_rank = ',' . session('user_rank') . ',';
    $favourable = [];
    if ($ru_id > 0) {
        $res = FavourableActivity::where(function ($query) use ($ru_id) {
            $query->where('user_id', $ru_id)
                ->orWhere('userFav_type', 1);
        });
    } else {
        $res = FavourableActivity::where('user_id', $ru_id);
    }

    $res = $res->where('review_status', 3)
        ->where('start_time', '<=', $gmtime)
        ->where('end_time', '>=', $gmtime);

    if (!empty($goods_id)) {
        $res = $res->whereRaw("CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'");
    }

    $res = $res->take(15);

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        if (empty($goods_id)) {
            foreach ($res as $rows) {
                if ($rows['userFav_type'] == 1) {
                    $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                } else {
                    $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                }
                $favourable[$rows['act_id']]['url'] = 'activity.php';
                $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                $favourable[$rows['act_id']]['type'] = 'favourable';
                $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
            }
        } else {
            $goods = Goods::select('goods_id', 'cat_id', 'brand_id')->where('goods_id', $goods_id);
            $goods = BaseRepository::getToArrayFirst($goods);

            $category_id = $goods['cat_id'] ?? 0;
            $brand_id = $goods['brand_id'] ?? 0;

            foreach ($res as $rows) {
                if ($rows['act_range'] == FAR_ALL) {
                    $mer_ids = true;
                    if ($mer_ids) {
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                    }
                } elseif ($rows['act_range'] == FAR_CATEGORY) {
                    $raw_id_list = explode(',', $rows['act_range_ext']);

                    foreach ($raw_id_list as $id) {
                        /**
                         * 当前分类下的所有子分类
                         * 返回一维数组
                         */
                        $cat_keys = $CategoryRep->getCatListChildren(intval($id));
                        $list_array[$rows['act_id']][$id] = $cat_keys;
                    }

                    $list_array = !empty($list_array) ? array_merge($raw_id_list, $list_array[$rows['act_id']]) : $raw_id_list;
                    $id_list = arr_foreach($list_array);
                    $id_list = array_unique($id_list);

                    $ids = join(',', array_unique($id_list));

                    if (strpos(',' . $ids . ',', ',' . $category_id . ',') !== false) {
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                    }
                } elseif ($rows['act_range'] == FAR_BRAND) {
                    $rows['act_range_ext'] = act_range_ext_brand($rows['act_range_ext'], $rows['userFav_type'], $rows['act_range']);
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $brand_id . ',') !== false) {
                        if ($rows['userFav_type'] == 1) {
                            $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                        } else {
                            $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                        }
                        $favourable[$rows['act_id']]['url'] = 'activity.php';
                        $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                        $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                        $favourable[$rows['act_id']]['type'] = 'favourable';
                        $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                    }
                } elseif ($rows['act_range'] == FAR_GOODS) {
                    if (strpos(',' . $rows['act_range_ext'] . ',', ',' . $goods_id . ',') !== false) {
                        $mer_ids = true;
                        if ($mer_ids) {
                            if ($rows['userFav_type'] == 1) {
                                $favourable[$rows['act_id']]['act_name'] = "[" . lang('common.general_audience') . "]" . $rows['act_name'];
                            } else {
                                $favourable[$rows['act_id']]['act_name'] = $rows['act_name'];
                            }
                            $favourable[$rows['act_id']]['url'] = 'activity.php';
                            $favourable[$rows['act_id']]['time'] = sprintf($GLOBALS['_LANG']['promotion_time'], TimeRepository::getLocalDate('Y-m-d', $rows['start_time']), TimeRepository::getLocalDate('Y-m-d', $rows['end_time']));
                            $favourable[$rows['act_id']]['sort'] = $rows['start_time'];
                            $favourable[$rows['act_id']]['type'] = 'favourable';
                            $favourable[$rows['act_id']]['act_type'] = $rows['act_type'];
                        }
                    }
                }
            }
        }
    }


    $sort_time = [];
    $arr = array_merge($snatch, $group, $auction, $package, $favourable);

    if ($arr) {
        foreach ($arr as $key => $value) {
            $sort_time[] = $value['sort'];
        }
        array_multisort($sort_time, SORT_NUMERIC, SORT_DESC, $arr);
    }

    return $arr;
}

/**
 * 获得指定分类下所有底层分类的ID
 *
 * @param int $cat
 * @param int $type
 * @param int $child_three
 * @param string $table
 * @param string $type_cat
 * @return string
 */
function get_children($cat = 0, $type = 0, $child_three = 0, $table = 'category', $type_cat = '')
{
    $supplierEnabled = CommonRepository::judgeSupplierEnabled();

    /**
     * 当前分类下的所有子分类
     * 返回一维数组
     */
    $cat_keys = app(CategoryService::class)->getArrayKeysCat($cat, 0, $table);

    if ($type != 2) {
        if (empty($type_cat)) {
            if ($type == 1) {
                $type_cat = 'gc.cat_id ';
            } elseif ($type == 3) {
                $type_cat = 'wc.cat_id ';
            } elseif ($type == 4) {
                if ($supplierEnabled) {
                    $type_cat = 'cat_id ';
                } else {
                    $type_cat = 'wholesale_cat_id ';
                }
            } elseif ($type == 5) {
                $type_cat = 'a.cat_id ';
            } else {
                $type_cat = 'g.cat_id ';
            }
        }

        if ($child_three == 1) {
            if ($cat) {
                return $type_cat . db_create_in($cat);
            } else {
                return $type_cat . db_create_in('');
            }
        } else {
            $cat = array_unique(array_merge([$cat], $cat_keys));

            if ($cat) {
                $cat = db_create_in($cat);
            } else {
                $cat = db_create_in('');
            }
            return $type_cat . $cat;
        }
    } else {
        $cat_keys = !empty($cat_keys) ? implode(",", $cat_keys) : '';
        $cat_keys = app(DscRepository::class)->delStrComma($cat_keys);

        return $cat_keys;
    }
}


/**
 * 获得指定文章分类下所有底层分类的ID
 *
 * @access  public
 * @param integer $cat 指定的分类ID
 *
 * @return void
 */
function get_article_children($cat = 0)
{
    return db_create_in(array_unique(array_merge([$cat], array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
}

/**
 * 获取邮件模板
 *
 * @access  public
 * @param:  $tpl_name[string]       模板代码
 *
 * @return array
 */
function get_mail_template($tpl_name)
{
    $row = MailTemplates::where('template_code', $tpl_name);
    $row = BaseRepository::getToArrayFirst($row);

    return $row;
}

/**
 * 记录订单操作记录
 *
 * @access  public
 * @param string $order_sn 订单编号
 * @param integer $order_status 订单状态
 * @param integer $shipping_status 配送状态
 * @param integer $pay_status 付款状态
 * @param string $note 备注
 * @param string $username 用户名，用户自己的操作则为 buyer
 * @param int $place
 * @param integer $confirm_take_time 确认收货时间
 * @return bool
 */
function order_action($order_sn = '', $order_status = 0, $shipping_status = 0, $pay_status = 0, $note = '', $username = '', $place = 0, $confirm_take_time = 0)
{
    if (empty($order_sn)) {
        return false;
    }

    if (!empty($confirm_take_time)) {
        $log_time = $confirm_take_time;
    } else {
        $log_time = TimeRepository::getGmTime();
    }

    if (empty($username)) {
        $admin_id = get_admin_id();

        $username = AdminUser::where('user_id', $admin_id)->value('user_name');
        $username = $username ? $username : '';
    }

    $order_id = OrderInfo::where('order_sn', $order_sn)->value('order_id');
    $order_id = $order_id ? $order_id : 0;

    if ($order_id > 0) {
        $place = !is_null($place) ? $place : '';
        $note = !is_null($note) ? $note : '';

        // 同一订单 订单状态不同
        $where = [
            'order_id' => $order_id,
            'order_status' => $order_status,
            'shipping_status' => $shipping_status,
            'pay_status' => $pay_status,
        ];
        $other = [
            'action_user' => $username,
            'action_place' => $place,
            'action_note' => $note,
            'log_time' => $log_time
        ];
        OrderAction::query()->updateOrInsert($where, $other);
        return true;
    }

    return false;
}

/**
 * 格式化商品价格
 *
 * @param int $price
 * @param bool $change_price
 * @return string
 * @throws Exception
 */
function price_format($price = 0, $change_price = true)
{
    return app(DscRepository::class)->getPriceFormat($price, $change_price);
}

/**
 * 返回订单虚拟商品是否货齐
 *
 * @param int $order_id
 * @return int
 */
function order_virtual_card_count($order_id = 0)
{
    $goods_list = OrderGoods::select('goods_id')->where('order_id', $order_id)
        ->where('is_real', 0)
        ->where('extension_code', 'virtual_card')
        ->whereRaw("(goods_number - send_number) > 0");

    $goods_list = BaseRepository::getToArrayGet($goods_list);

    $is_number = 0;
    if ($goods_list) {
        foreach ($goods_list as $key => $row) {
            $count = VirtualCard::where('goods_id', $row['goods_id'])
                ->where('is_saled', 0)
                ->where('order_sn', '')
                ->count();

            if (!$count) {
                $is_number = 1;
                continue;
            }
        }
    }

    return $is_number;
}

/**
 * 返回订单中的虚拟商品
 *
 * @access  public
 * @param int $order_id 订单id值
 * @param bool $shipping 是否已经发货
 *
 * @return []
 */
function get_virtual_goods($order_id, $shipping = false)
{
    if ($shipping) {
        $res = OrderGoods::select('goods_id', 'goods_name', 'send_number AS num', 'extension_code')->where('order_id', $order_id)
            ->where('extension_code', 'virtual_card');
    } else {
        $res = OrderGoods::selectRaw("goods_id, goods_name, (goods_number - send_number) AS num, extension_code")
            ->where('order_id', $order_id)
            ->where('is_real', 0)
            ->where('extension_code', 'virtual_card')
            ->whereRaw("(goods_number - send_number) > 0");
    }

    $res = BaseRepository::getToArrayGet($res);

    $virtual_goods = [];
    if ($res) {
        foreach ($res as $row) {
            $virtual_goods[$row['extension_code']][] = ['goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']];
        }
    }

    return $virtual_goods;
}

/**
 *  虚拟商品发货
 *
 * @access  public
 * @param array $virtual_goods 虚拟商品数组
 * @param string $msg 错误信息
 * @param string $order_sn 订单号。
 * @param bool $return_result
 * @param string $process 设定当前流程：split，发货分单流程；other，其他，默认。
 * @return array|bool
 * @throws Exception
 */
function virtual_goods_ship(&$virtual_goods, &$msg, $order_sn, $return_result = false, $process = 'other')
{
    $virtual_card = [];
    if ($virtual_goods) {
        foreach ($virtual_goods as $code => $goods_list) {
            /* 只处理虚拟卡 */
            if ($code == 'virtual_card') {
                foreach ($goods_list as $goods) {
                    if (virtual_card_shipping($goods, $order_sn, $msg, $process)) {
                        if ($return_result) {
                            $virtual_card[] = ['goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => virtual_card_result($order_sn, $goods)];
                        }
                    } else {
                        return false;
                    }
                }
            }
        }
    }

    return $virtual_card;
}

/**
 *  虚拟卡发货
 *
 * @access  public
 * @param array $goods 商品详情数组
 * @param string $order_sn 本次操作的订单
 * @param string $msg 返回信息
 * @param string $process 设定当前流程：split，发货分单流程；other，其他，默认。
 * @return bool
 * @throws Exception
 */
function virtual_card_shipping($goods = [], $order_sn = '', &$msg, $process = 'other')
{
    /* 包含加密解密函数所在文件 */
    load_helper('code');

    /* 检查有没有缺货 */
    $num = VirtualCard::where('goods_id', $goods['goods_id'])
        ->where('is_saled', 0)
        ->count();

    if ($num < $goods['num']) {
        $msg .= sprintf(trans('common.virtual_card_oos'), $goods['goods_name']);
        return false;
    }

    /* 取出卡片信息 */
    $arr = VirtualCard::where('goods_id', $goods['goods_id'])
        ->where('is_saled', 0)
        ->take($goods['num']);

    $arr = BaseRepository::getToArrayGet($arr);

    $card_ids = [];
    $cards = [];
    if ($arr) {
        foreach ($arr as $virtual_card) {
            $card_info = [];

            $card_info['card_sn'] = $virtual_card['card_sn'];

            /* 卡号和密码解密 */
            if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY)) {
                $card_info['card_password'] = dsc_decrypt($virtual_card['card_password']);
            } elseif ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY)) {
                $card_info['card_password'] = dsc_decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
            }

            $card_info['end_date'] = TimeRepository::getLocalDate(config('shop.date_format', 'Y-m-d'), $virtual_card['end_date']);
            $card_ids[] = $virtual_card['card_id'];
            $cards[] = $card_info;
        }
    }

    /* 标记已经取出的卡片 */
    $other = [
        'is_saled' => 1,
        'order_sn' => $order_sn
    ];
    $res = VirtualCard::whereIn('card_id', $card_ids)->update($other);
    if (!$res) {
        $msg .= 'update error';
        return false;
    }

    $order = [];
    if ($res) {

        /* 减去商品库存 */
        Goods::where('goods_id', $goods['goods_id'])->where('goods_number', '>=', $goods['num'])->decrement('goods_number', $goods['num']);

        /* 获取订单信息 */
        $order = DB::table('order_info')->select('order_id', 'order_sn', 'email', 'consignee', 'user_id')->where('order_sn', $order_sn)->first();
        $order = collect($order)->toArray() ?? [];

        if (!$order) {
            $msg .= 'order error';
            return false;
        }

        /* 更新订单信息 */
        if ($process == 'split') {
            $res = OrderGoods::where('order_id', $order['order_id'])
                ->where('goods_id', $goods['goods_id'])
                ->increment('send_number', $goods['num']);
        } else {
            $res = OrderGoods::where('order_id', $order['order_id'])
                ->where('goods_id', $goods['goods_id'])
                ->update(['send_number' => $goods['num']]);
        }

        if (!$res) {
            $msg .= 'update error';
            return false;
        }
    }

    if (!empty($cards) && !empty($order['email']) && config('shop.send_ship_email') == 1) {
        /* 发货时 发送邮件 */
        $tpl = new \App\Libraries\Template();

        $tpl->assign('virtual_card', $cards);
        $tpl->assign('order', $order);
        $tpl->assign('goods', $goods);

        $tpl->assign('send_time', TimeRepository::getLocalDate('Y-m-d H:i:s'));
        $tpl->assign('shop_name', config('shop.shop_name', ''));
        $tpl->assign('send_date', TimeRepository::getLocalDate('Y-m-d'));
        $tpl->assign('sent_date', TimeRepository::getLocalDate('Y-m-d'));

        // 邮件模板内容
        $template = DB::table('mail_templates')->where('template_code', 'virtual_card')->select('is_html', 'template_subject', 'template_content')->first();
        $template = collect($template)->toArray() ?? [];

        if (empty($template)) {
            $msg .= 'send error';
            return false;
        }

        $content = $tpl->fetch('str:' . $template['template_content']);
        CommonRepository::sendEmail($order['consignee'], $order['email'], $template['template_subject'], $content, $template['is_html']);
    }

    return true;
}

/**
 * 返回虚拟卡信息
 *
 * @param string $order_sn
 * @param array $goods
 * @return array
 */
function virtual_card_result($order_sn = '', $goods = [])
{
    /* 包含加密解密函数所在文件 */
    load_helper('code');

    /* 获取已经发送的卡片数据 */
    $res = VirtualCard::where('goods_id', $goods['goods_id'])
        ->where('order_sn', $order_sn);

    $res = BaseRepository::getToArrayGet($res);

    $cards = [];
    if ($res) {
        foreach ($res as $row) {
            /* 卡号和密码解密 */
            if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY)) {
                $row['card_password'] = dsc_decrypt($row['card_password']);
            } elseif ($row['crc32'] == crc32(OLD_AUTH_KEY)) {
                $row['card_password'] = dsc_decrypt($row['card_password'], OLD_AUTH_KEY);
            }

            $cards[] = ['card_sn' => $row['card_sn'], 'card_password' => $row['card_password'], 'end_date' => TimeRepository::getLocalDate(config('shop.date_format'), $row['end_date'])];
        }
    }

    return $cards;
}

/**
 * 获取指定 id snatch 活动的结果
 *
 * @access  public
 * @param int $id snatch_id
 *
 * @return  array           array(user_name, bie_price, bid_time, num)
 *                          num通常为1，如果为2表示有2个用户取到最小值，但结果只返回最早出价用户。
 */
function get_snatch_result($id)
{
    $rec = SnatchLog::selectRaw("user_id, bid_price, bid_time, COUNT(*) AS num")
        ->where('snatch_id', $id);

    $rec = $rec->with([
        'getUsers' => function ($query) {
            $query->select('user_id', 'user_name', 'email');
        }
    ]);

    $rec = $rec->orderByRaw("num, bid_price, bid_time asc");

    $rec = BaseRepository::getToArrayFirst($rec);

    if ($rec) {
        $user_info = $rec['get_users'] ? $rec['get_users'] : [];

        $rec['user_name'] = $user_info ? $user_info['user_name'] : '';
        $rec['email'] = $user_info ? $user_info['email'] : '';

        if (isset($GLOBALS['_CFG']['show_mobile']) && $GLOBALS['_CFG']['show_mobile'] == 0) {
            $rec['user_name'] = app(DscRepository::class)->stringToStar($rec['user_name']);
        }

        $rec['bid_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $rec['bid_time']);
        $rec['formated_bid_price'] = app(DscRepository::class)->getPriceFormat($rec['bid_price'], false);

        /* 活动信息 */
        $row = GoodsActivity::where('review_status', 3)->where('act_id', $id)->where('act_type', GAT_SNATCH)->value('ext_info');
        $info = $row ? unserialize($row) : [];

        if ($info && !empty($info['max_price'])) {
            $rec['buy_price'] = ($rec['bid_price'] > $info['max_price']) ? $info['max_price'] : $rec['bid_price'];
        } else {
            $rec['buy_price'] = $rec['bid_price'];
        }


        /* 检查订单 */
        $rec['order_count'] = OrderInfo::where('extension_code', 'snatch')
            ->where('extension_id', $id)
            ->whereIn('order_status', [OS_CONFIRMED, OS_UNCONFIRMED, OS_SPLITED, OS_SPLITING_PART])
            ->count();
    }

    return $rec;
}

/**
 *  清除指定后缀的模板缓存或编译文件
 *
 * @access  public
 * @param bool $is_cache 是否清除缓存还是清出编译文件
 * @param string $ext 需要删除的文件名，不包含后缀
 *
 * @return int        返回清除的文件个数
 */
function clear_tpl_files($is_cache = true, $ext = '', $filename = '')
{
    if (empty($filename)) {
        $filename = "admin";
    }
    //ecmoban模板堂 --zhuo memcached start
    if ($GLOBALS['_CFG']['open_memcached'] == 1) {
        return $GLOBALS['cache']->clear();
    }
    //ecmoban模板堂 --zhuo memcached end

    $dirs = [];

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0) {
        $tmp_dir = DATA_DIR;
    } else {
        $tmp_dir = 'temp';
    }
    if ($is_cache) {
        $cache_dir = storage_path('framework/' . $tmp_dir . '/caches/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/query_caches/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/static_caches/');
        for ($i = 0; $i < 16; $i++) {
            $hash_dir = $cache_dir . dechex($i);
            $dirs[] = $hash_dir . '/';
        }
    } else {
        $dirs[] = storage_path('framework/' . $tmp_dir . '/compiled/');
        $dirs[] = storage_path('framework/' . $tmp_dir . '/compiled/' . $filename . '/');
    }

    $str_len = strlen($ext);
    $count = 0;

    foreach ($dirs as $dir) {
        $folder = @opendir($dir);

        if ($folder === false) {
            continue;
        }

        while ($file = readdir($folder)) {
            if ($file == '.' || $file == '..' || $file == 'index.htm' || $file == 'index.html' || $file == '.gitignore') {
                continue;
            }
            if (is_file($dir . $file)) {
                /* 如果有文件名则判断是否匹配 */
                $pos = ($is_cache) ? strrpos($file, '_') : strrpos($file, '.');

                if ($str_len > 0 && $pos !== false) {
                    $ext_str = substr($file, 0, $pos);

                    if ($ext_str == $ext) {
                        if (@unlink($dir . $file)) {
                            $count++;
                        }
                    }
                } else {
                    if (@unlink($dir . $file)) {
                        $count++;
                    }
                }
            }
        }
        closedir($folder);
    }

    return $count;
}

/**
 * 清除模版编译文件
 *
 * @access  public
 * @param mix $ext 模版文件名， 不包含后缀
 * @return  void
 */
function clear_compiled_files($ext = '')
{
    return clear_tpl_files(false, $ext);
}

/**
 * 清除缓存文件
 *
 * @access  public
 * @param mix $ext 模版文件名， 不包含后缀
 * @return  void
 */
function clear_cache_files($ext = '')
{
    return clear_tpl_files(true, $ext);
}

/**
 * 清除模版编译和缓存文件
 *
 * @access  public
 * @param mix $ext 模版文件名后缀
 * @return  void
 */
function clear_all_files($ext = '', $filename = '')
{
    return clear_tpl_files(false, $ext, $filename) + clear_tpl_files(true, $ext, $filename);
}

/**
 * 创建分页的列表
 *
 * @access  public
 * @param integer $count
 * @return  string
 */
function smarty_create_pages($params)
{
    extract($params);

    $str = '';
    $len = 10;

    if (empty($page)) {
        $page = 1;
    }

    if (!empty($count)) {
        $step = 1;
        $str .= "<option value='1'>1</option>";

        for ($i = 2; $i < $count; $i += $step) {
            $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
            $str .= "<option value='$i'";
            $str .= $page == $i ? " selected='true'" : '';
            $str .= ">$i</option>";
        }

        if ($count > 1) {
            $str .= "<option value='$count'";
            $str .= $page == $count ? " selected='true'" : '';
            $str .= ">$count</option>";
        }
    }

    return $str;
}

/**
 * 格式化重量：小于1千克用克表示，否则用千克表示
 * @param float $weight 重量
 * @return  string  格式化后的重量
 */
function formated_weight($weight)
{
    $weight = round(floatval($weight), 3);
    if ($weight > 0) {
        if ($weight < 1) {
            /* 小于1千克，用克表示 */
            return intval($weight * 1000) . $GLOBALS['_LANG']['gram'];
        } else {
            /* 大于1千克，用千克表示 */
            return $weight . $GLOBALS['_LANG']['kilogram'];
        }
    } else {
        return 0;
    }
}

/**
 * 记录帐户变动
 *
 * @param int $user_id 用户id
 * @param int $user_money 可用余额变动
 * @param int $frozen_money 冻结余额变动
 * @param int $rank_points 等级积分变动
 * @param int $pay_points 消费积分变动
 * @param string $change_desc 变动说明
 * @param int $change_type 变动类型：参见常量文件
 * @param int $order_type
 * @param int $deposit_fee
 * @throws Exception
 */
function log_account_change($user_id = 0, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = ACT_OTHER, $order_type = 0, $deposit_fee = 0)
{
    $is_go = true;
    $is_user_money = 0;
    $is_pay_points = 0;
    $order_sn = '';

    //控制只有后台执行，前台不操作以下程序
    if ($change_desc && $order_type) {
        $change_desc_arr = $change_desc ? explode(" ", $change_desc) : [];

        if (count($change_desc_arr) >= 2) {
            $order_sn = !empty($change_desc_arr[1]) ? $change_desc_arr[1] : '';

            if (!empty($order_sn)) {
                $order_res = OrderInfo::select(['order_id', 'main_order_id'])->where('order_sn', $order_sn);
                $order_res = BaseRepository::getToArrayFirst($order_res);
            } else {
                $order_res = [];
            }

            if (empty($order_res)) {
                $is_go = false;
            }

            if ($order_res) {
                if ($order_res['main_order_id'] > 0) {  //操作无效或取消订单时，先查询该订单是否有主订单

                    $ordor_main = OrderInfo::select('order_sn')->where('order_id', $order_res['main_order_id']);
                    $ordor_main = BaseRepository::getToArrayFirst($ordor_main);

                    if ($ordor_main) {
                        $order_surplus_desc = sprintf(lang('user.return_order_surplus'), $ordor_main['order_sn']);
                        $order_integral_desc = sprintf(lang('user.return_order_integral'), $ordor_main['order_sn']);
                    } else {
                        $order_surplus_desc = '';
                        $order_integral_desc = '';
                    }

                    //查询该订单的主订单是否已操作过无效或取消订单
                    $change_desc = [$order_surplus_desc, $order_integral_desc];

                    $log_res = [];
                    if ($change_desc) {
                        $log_res = AccountLog::select('log_id')->whereIn('change_desc', $change_desc);
                        $log_res = BaseRepository::getToArrayGet($log_res);
                    }

                    if ($log_res) {
                        $is_go = false;
                    }
                } else {
                    if ($order_res && $order_res['order_id'] > 0) {
                        $main_order_res = OrderInfo::select('order_id', 'order_sn')->where('main_order_id', $order_res['order_id']);
                        $main_order_res = BaseRepository::getToArrayGet($main_order_res);

                        if ($main_order_res > 0) {
                            foreach ($main_order_res as $key => $row) {
                                $order_surplus_desc = sprintf(lang('user.return_order_surplus'), $row['order_sn']);
                                $order_integral_desc = sprintf(lang('user.return_order_integral'), $row['order_sn']);

                                $main_change_desc = [$order_surplus_desc, $order_integral_desc];
                                $parent_account_log = AccountLog::select(['user_money', 'pay_points'])->whereIn('change_desc', $main_change_desc);
                                $parent_account_log = BaseRepository::getToArrayGet($parent_account_log);

                                if ($parent_account_log) {
                                    if ($user_money) {
                                        $is_user_money += $parent_account_log[0]['user_money'];
                                    }

                                    if ($pay_points) {
                                        $is_pay_points += $parent_account_log[1]['pay_points'];
                                    }
                                }
                            }
                        }
                    }

                    if ($user_money) {
                        $user_money -= $is_user_money;
                    }

                    if ($pay_points) {
                        $pay_points -= $is_pay_points;
                    }
                }
            }
        }
    } /**
     * 判断是否是支付订单操作
     * 【订单号不能为空】
     *
     */
    elseif ($change_desc) {
        if (strpos($change_desc, '：') !== false) {
            $change_desc_arr = explode("：", $change_desc);
        } else {
            $change_desc_arr = explode(" ", $change_desc);
        }

        if (count($change_desc_arr) >= 2) {
            if (!empty($change_desc_arr[0]) && ($change_desc_arr[0] == '支付订单' || $change_desc_arr[0] == '追加使用余额支付订单')) {
                if (!empty($change_desc_arr[1])) {
                    $change_desc_arr[1] = trim($change_desc_arr[1]);
                }

                $order_sn = !empty($change_desc_arr[1]) ? $change_desc_arr[1] : '';

                if ($order_sn) {
                    $order_res = OrderInfo::where('order_sn', $order_sn);
                    $order_res = BaseRepository::getToArrayFirst($order_res);
                } else {
                    $order_res = [];
                }

                if (empty($order_res)) {
                    $is_go = false;
                }
            }
        }
    }

    if (!empty($order_sn)) {
        $is_go = app(DscRepository::class)->filterAccountChangeOrder($order_sn, $user_id, $user_money, $pay_points, $is_go);
    }

    if ($is_go && ($user_money || $frozen_money || $rank_points || $pay_points)) {
        if (is_array($change_desc)) {
            $change_desc = implode('<br/>', $change_desc);
        }

        /* 插入帐户变动记录 */
        $account_log = [
            'user_id' => $user_id,
            'user_money' => $user_money,
            'frozen_money' => $frozen_money,
            'rank_points' => $rank_points,
            'pay_points' => $pay_points,
            'change_time' => TimeRepository::getGmTime(),
            'change_desc' => $change_desc,
            'change_type' => $change_type,
            'deposit_fee' => $deposit_fee
        ];

        AccountLog::insert($account_log);

        /* 更新用户信息 */
        $user_money = $user_money + $deposit_fee;
        $update_log = [
            'frozen_money' => DB::raw("frozen_money  + ('$frozen_money')"),
            'pay_points' => DB::raw("pay_points  + ('$pay_points')"),
            'rank_points' => DB::raw("rank_points  + ('$rank_points')")
        ];

        $r = Users::where('user_id', $user_id)->increment('user_money', $user_money, $update_log);

        if ($r) {
            //会员余额变动短信通知
            $sms_change_user_money = config('shop.sms_change_user_money', 0);
            if (!empty($user_money) && !empty($sms_change_user_money)) {
                SendSmsChangUserMoney::dispatch($user_id);
            }
        }

        if (!app(UserRankService::class)->judgeUserSpecialRank($user_id)) {

            /* 更新会员当前等级 start */
            $user_rank_points = Users::where('user_id', $user_id)->value('rank_points');
            $user_rank_points = $user_rank_points ? $user_rank_points : 0;

            $rank_row = [];
            if ($user_rank_points >= 0) {
                //1.4.3 会员等级修改（成长值只有下限）
                $rank_row = app(UserRankService::class)->getUserRankByPoint($user_rank_points);
            }

            if ($rank_row) {
                $rank_row['discount'] = $rank_row['discount'] / 100.00;
            } else {
                $rank_row['discount'] = 1;
                $rank_row['rank_id'] = 0;
            }

            /* 更新会员当前等级 end */
            Users::where('user_id', $user_id)->update(['user_rank' => $rank_row['rank_id']]);

            $userRank = [
                'user_rank' => $rank_row['rank_id'],
                'discount' => $rank_row['discount']
            ];
            session($userRank);
        }
    }
}

/**
 * 商家帐户变动
 * @param int $ru_id
 * @param int $seller_money
 * @param int $frozen_money
 * @return bool
 */
function log_seller_account_change($ru_id = 0, $seller_money = 0, $frozen_money = 0)
{
    if (empty($ru_id)) {
        return false;
    }

    /* 更新商家账户信息 */
    if ($seller_money || $frozen_money) {
        $other = [
            'seller_money' => "seller_money  + ('$seller_money')",
            'frozen_money' => "frozen_money  + ('$frozen_money')"
        ];
        $other = BaseRepository::getDbRaw($other);

        return SellerShopinfo::where('ru_id', $ru_id)->update($other);
    }

    return false;
}

/**
 * 商家帐户变动记录
 * @param int $ru_id
 * @param int $user_money
 * @param int $frozen_money
 * @param string $change_desc
 * @param int $change_type
 */
function merchants_account_log($ru_id = 0, $user_money = 0, $frozen_money = 0, $change_desc = '', $change_type = 1)
{
    if (empty($ru_id)) {
        return false;
    }

    if ($user_money || $frozen_money) {
        $other = [
            'user_id' => $ru_id,
            'user_money' => $user_money,
            'frozen_money' => $frozen_money,
            'change_time' => TimeRepository::getGmTime(),
            'change_desc' => $change_desc,
            'change_type' => $change_type
        ];
        MerchantsAccountLog::insert($other);
    }
}


/**
 * 获得指定分类下的子分类的数组 临时函数  by kong
 *
 * @access  public
 * @param int $cat_id 分类的ID
 * @param int $selected 当前选中分类的ID
 * @param boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level 限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function article_cat_list_new($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
    static $res = null;

    if ($res === null) {
        $data = read_static_cache('art_cat_pid_releate');

        if ($data === false) {
            $res = ArticleCat::whereRaw(1)
                ->orderByRaw("parent_id, sort_order ASC");

            $res = $res->with([
                'getArticleCatChildrenList',
                'getArticleFirst' => function ($query) {
                    $query->selectRaw("article_id, content, add_time, cat_id, count(*) AS aricle_num, description");
                }
            ]);

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $has_children = $row['get_article_cat_children_list'] ? collect($row['get_article_cat_children_list'])->count() : 0;
                    $res[$key]['has_children'] = $has_children;

                    $aricle = $row['get_article_first'] ? $row['get_article_first'] : [];

                    $res[$key]['aricle_num'] = $aricle ? $aricle['aricle_num'] : 0;
                    $res[$key]['description'] = $aricle ? $aricle['description'] : '';
                }
            }

            write_static_cache('art_cat_pid_releate', $res);
        } else {
            $res = $data;
        }
    }

    if (empty($res) == true) {
        return $re_type ? '' : [];
    }

    $options = article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

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

    $pre_key = 0;
    foreach ($options as $key => $value) {
        $options[$key]['has_children'] = 1;
        if ($pre_key > 0) {
            if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id']) {
                $options[$pre_key]['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<li><a href="javascript:;" cat_type="' . $var['cat_type'] . '" data-value="' . $var['cat_id'] . '" ';
            $select .= ' cat_type="' . $var['cat_type'] . '" class="ftx-01">';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes(str_replace("\r\n", "", $var['cat_name']))) . '</a></li>';
        }

        return $select;
    }
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param int $cat_id 分类的ID
 * @param int $selected 当前选中分类的ID
 * @param boolean $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param int $level 限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
    static $res = null;

    if ($res === null) {
        $data = read_static_cache('art_cat_pid_releate');

        if ($data === false) {
            $res = ArticleCat::whereRaw(1);

            $res = $res->with([
                'getArticleCatChildrenList',
                'getArticleFirst' => function ($query) {
                    $query->selectRaw("cat_id, count(*) AS aricle_num, description");
                }
            ]);

            $res = $res->orderByRaw("parent_id, sort_order ASC");

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $has_children = $row['get_article_cat_children_list'] ? collect($row['get_article_cat_children_list'])->count() : 0;
                    $res[$key]['has_children'] = $has_children;

                    $aricle = $row['get_article_first'] ? $row['get_article_first'] : [];

                    $res[$key]['aricle_num'] = $aricle ? $aricle['aricle_num'] : 0;
                    $res[$key]['description'] = $aricle ? $aricle['description'] : '';
                }
            }

            write_static_cache('art_cat_pid_releate', $res);
        } else {
            $res = $data;
        }
    }

    if (empty($res) == true) {
        return $re_type ? '' : [];
    }

    $options = article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

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

    $pre_key = 0;
    foreach ($options as $key => $value) {
        $options[$key]['has_children'] = 1;
        if ($pre_key > 0) {
            if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id']) {
                $options[$pre_key]['has_children'] = 1;
            }
        }
        $pre_key = $key;
    }

    if ($re_type == true) {
        $select = '';
        foreach ($options as $var) {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ' cat_type="' . $var['cat_type'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name'])) . '</option>';
        }

        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options[$key]['url'] = app(DscRepository::class)->buildUri('article_cat', ['acid' => $value['cat_id']], $value['cat_name']);
        }
        return $options;
    }
}

/**
 * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param int $cat_id 上级分类ID
 * @param array $arr 含有所有分类的数组
 * @param int $level 级别
 * @return  void
 */
function article_cat_options($spec_cat_id, $arr)
{
    static $cat_options = [];

    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = [];
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
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level'])
            ) {
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
 * 是否存在规格
 *
 * @param $goods_attr_id_array
 * @param string $sort
 * @return bool
 */
function is_spec($goods_attr_id_array, $sort = 'asc')
{
    $list = app(GoodsAttrService::class)->sortGoodsAttrIdArray($goods_attr_id_array, $sort);

    if (!empty($list)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取指定id package 的信息
 *
 * @param $id
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @param string $path
 * @return mixed
 */
function get_package_info($id, $warehouse_id = 0, $area_id = 0, $area_city = 0, $path = '')
{
    $id = is_numeric($id) ? intval($id) : 0;
    $now = TimeRepository::getGmTime();

    $package = GoodsActivity::selectRaw("*, act_id AS id, user_id AS ru_id, act_name AS package_name")->where('act_id', $id)
        ->where('act_type', GAT_PACKAGE);


    if (empty($path)) {
        $package = $package->where('review_status', 3);
    }

    $package = BaseRepository::getToArrayFirst($package);

    if (!empty($package)) {
        /* 将时间转成可阅读格式 */
        if ($package['start_time'] <= $now && $package['end_time'] >= $now) {
            $package['is_on_sale'] = "1";
        } else {
            $package['is_on_sale'] = "0";
        }
        $package['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $package['start_time']);
        $package['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $package['end_time']);
        $row = unserialize($package['ext_info']);
        unset($package['ext_info']);
        if ($row) {
            foreach ($row as $key => $val) {
                $package[$key] = $val;
            }
        }
    }

    $goods_res = Goods::select('goods_id', 'goods_sn', 'goods_name', 'model_price', 'market_price', 'shop_price', 'promote_price', 'goods_thumb', 'is_real', 'promote_start_date', 'promote_end_date', 'cloud_id', 'goods_number AS stock_number')
        ->where('is_on_sale', 1)
        ->where('is_alone_sale', 1)
        ->where('is_delete', 0)
        ->whereHasIn('getPackageGoods', function ($query) use ($id) {
            $query->where('package_id', $id);
        });

    if (config('shop.review_goods') == 1) {
        $goods_res = $goods_res->whereIn('review_status', [3, 4, 5]);
    }

    $goods_res = app(DscRepository::class)->getAreaLinkGoods($goods_res, $area_id);

    $where = [
        'warehouse_id' => $warehouse_id,
        'area_id' => $area_id,
        'area_city' => $area_city,
        'area_pricetype' => $GLOBALS['_CFG']['area_pricetype']
    ];

    $user_rank = session('user_rank');
    $goods_res = $goods_res->with([
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
        },
        'getPackageGoods' => function ($query) use ($id) {
            $query->select('package_id', 'goods_id', 'product_id', 'goods_number', 'admin_id')
                ->where('package_id', $id);
        }
    ]);

    $goods_res = BaseRepository::getToArrayGet($goods_res);

    $market_price = 0;
    $real_goods_count = 0;
    $virtual_goods_count = 0;

    if ($goods_res) {
        foreach ($goods_res as $key => $val) {
            $val = $val['get_package_goods'] ? array_merge($val, $val['get_package_goods']) : $val;
            $goods_res[$key] = $val;

            if ($val['cloud_id']) {
                if ($val['product_id']) {
                    $goods_number = app(JigonManageService::class)->jigonGoodsNumber(['product_id' => $val['product_id']]);
                } else {
                    $goods_number = $val['stock_number'];
                }

                $goods_res[$key]['stock_number'] = $goods_number;
            } else {
                if ($val['product_id']) {

                    /* 普通商品(默认模式) */
                    $product_number = Products::where('product_id', $val['product_id'])->value('product_number');
                    $product_number = $product_number ? $product_number : 0;
                } else {
                    $product_number = $val['stock_number'];
                }

                $goods_res[$key]['stock_number'] = $product_number;
            }

            $price = [
                'model_price' => isset($val['model_price']) ? $val['model_price'] : 0,
                'user_price' => isset($val['get_member_price']['user_price']) ? $val['get_member_price']['user_price'] : 0,
                'percentage' => isset($val['get_member_price']['percentage']) ? $val['get_member_price']['percentage'] : 0,
                'warehouse_price' => isset($val['get_warehouse_goods']['warehouse_price']) ? $val['get_warehouse_goods']['warehouse_price'] : 0,
                'region_price' => isset($val['get_warehouse_area_goods']['region_price']) ? $val['get_warehouse_area_goods']['region_price'] : 0,
                'shop_price' => isset($val['shop_price']) ? $val['shop_price'] : 0,
                'warehouse_promote_price' => isset($val['get_warehouse_goods']['warehouse_promote_price']) ? $val['get_warehouse_goods']['warehouse_promote_price'] : 0,
                'region_promote_price' => isset($val['get_warehouse_area_goods']['region_promote_price']) ? $val['get_warehouse_area_goods']['region_promote_price'] : 0,
                'promote_price' => isset($val['promote_price']) ? $val['promote_price'] : 0,
            ];

            $price = app(GoodsCommonService::class)->getGoodsPrice($price, session('discount'), $val);

            $val['shop_price'] = $price['shop_price'];
            $val['promote_price'] = $price['promote_price'];

            if ($val['promote_price'] > 0) {
                $promote_price = app(GoodsCommonService::class)->getBargainPrice($val['promote_price'], $val['promote_start_date'], $val['promote_end_date']);
            } else {
                $promote_price = 0;
            }

            $goods_res[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($val['goods_thumb']);
            $goods_res[$key]['market_price'] = $val['market_price'];
            $goods_res[$key]['market_price_format'] = app(DscRepository::class)->getPriceFormat($val['market_price']);
            $goods_res[$key]['rank_price_format'] = app(DscRepository::class)->getPriceFormat($val['shop_price']);
            $goods_res[$key]['promote_price_format'] = ($promote_price > 0) ? app(DscRepository::class)->getPriceFormat($promote_price) : '';
            $market_price += $val['market_price'] * $val['goods_number'];

            /* 统计实体商品和虚拟商品的个数 */
            if ($val['is_real']) {
                $real_goods_count++;
            } else {
                $virtual_goods_count++;
            }
        }
    }

    if ($real_goods_count > 0) {
        $package['is_real'] = 1;
    } else {
        $package['is_real'] = 0;
    }

    $package['activity_thumb'] = $package['activity_thumb'] ? app(DscRepository::class)->getImagePath($package['activity_thumb']) : '';
    $package['goods_list'] = $goods_res;
    $package['market_package'] = $market_price;
    $package['market_package_format'] = app(DscRepository::class)->getPriceFormat($market_price);
    $package['package_price_format'] = app(DscRepository::class)->getPriceFormat($package['package_price']);

    return $package;
}

/**
 * 取商品的下拉框Select列表
 *
 * @param int $goods_id 商品id
 *
 * @return  array
 */
function get_good_products_select($goods_id)
{
    $return_array = [];

    $products = app(GoodsProdutsService::class)->getGoodProducts($goods_id);

    if (empty($products)) {
        return $return_array;
    }

    foreach ($products as $value) {
        $return_array[$value['product_id']] = $value['goods_attr_str'];
    }

    return $return_array;
}

/**
 * 调用array_combine函数
 *
 * @param array $keys
 * @param array $values
 *
 * @return  $combined
 */
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        if (!is_array($keys)) {
            user_error('array_combine() expects parameter 1 to be array, ' .
                gettype($keys) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_array($values)) {
            user_error('array_combine() expects parameter 2 to be array, ' .
                gettype($values) . ' given', E_USER_WARNING);
            return;
        }

        $key_count = count($keys);
        $value_count = count($values);
        if ($key_count !== $value_count) {
            user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
            return false;
        }

        if ($key_count === 0 || $value_count === 0) {
            user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
            return false;
        }

        $keys = array_values($keys);
        $values = array_values($values);

        $combined = [];
        for ($i = 0; $i < $key_count; $i++) {
            $combined[$keys[$i]] = $values[$i];
        }

        return $combined;
    }
}

//ecmoban模板堂 --zhuo start
function get_class_nav($cat_id, $table = 'category')
{
    if ($table == 'merchants_category') {
        $res = MerchantsCategory::where('cat_id', $cat_id);
    } else {
        $res = Category::where('cat_id', $cat_id);
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [
        'catId' => ''
    ];
    if ($res) {
        foreach ($res as $key => $row) {
            $arr[$key]['cat_id'] = $row['cat_id'];
            $arr[$key]['cat_name'] = $row['cat_name'];
            $arr[$key]['parent_id'] = $row['parent_id'];

            $arr['catId'] .= $row['cat_id'] . ",";
            $arr[$key]['child'] = get_parent_child($row['cat_id'], $table);

            if (empty($arr[$key]['child']['catId'])) {
                $arr['catId'] = $arr['catId'];
            } else {
                $arr['catId'] .= $arr[$key]['child']['catId'];
            }
        }
    }

    return $arr;
}

function get_parent_child($parent_id = 0, $table = 'category')
{
    if ($table == 'merchants_category') {
        $res = MerchantsCategory::where('parent_id', $parent_id)->where('is_show', 1);
    } else {
        $res = Category::where('parent_id', $parent_id)->where('is_show', 1);
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [
        'catId' => ''
    ];
    if ($res) {
        foreach ($res as $key => $row) {
            $arr[$key]['cat_id'] = $row['cat_id'];
            $arr[$key]['cat_name'] = $row['cat_name'];
            $arr[$key]['parent_id'] = $row['parent_id'];

            $arr['catId'] .= $row['cat_id'] . ",";
            $arr[$key]['child'] = get_parent_child($row['cat_id'], $table);
            $arr['catId'] .= $arr[$key]['child']['catId'];
        }
    }

    return $arr;
}

/**
 * 查询扩展分类商品id
 *
 * @param int cat_id
 *
 * @return int extentd_count
 * by guan
 */
function get_goodsCat_num($cat_id, $goods_ids = [], $ru_id = -1)
{
    if (empty($cat_id)) {
        return 0;
    }

    $cat_id = !is_array($cat_id) ? explode(",", $cat_id) : $cat_id;
    $goods_ids = !is_array($goods_ids) ? explode(",", $goods_ids) : $goods_ids;

    $cat_goods = GoodsCat::whereIn('cat_id', $cat_id);

    $cat_goods = $cat_goods->whereHasIn('getGoods', function ($query) use ($ru_id) {
        $query = $query->where('is_on_sale', 1)
            ->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->where('is_show', 1);

        if ($ru_id > -1) {
            $query = $query->where('user_id', $ru_id);
        }
    });

    if ($goods_ids) {
        $cat_goods = $cat_goods->whereNotIn('goods_id', $goods_ids);
    }

    $count = $cat_goods->count();

    return $count;
}

//guan start end

/**
 * 查询店铺分类
 */
function get_fine_store_category($options, $web_type, $array_type = 0, $ru_id)
{
    $cat_array = [];
    if ($web_type == 'admin' || $web_type == 'goodsInfo') {
        $store_cat = read_static_cache('merchants_category_fine');

        if ($store_cat === false) {
            $store_cat = MerchantsCategory::whereRaw(1);
            $store_cat = BaseRepository::getToArrayGet($store_cat);

            write_static_cache('merchants_category_fine', $store_cat);
        }

        if ($store_cat) {
            foreach ($store_cat as $row) {
                $cat_array[$row['cat_id']]['cat_id'] = $row['cat_id'];
                $cat_array[$row['cat_id']]['user_id'] = $row['user_id'];
            }
        }
    }

    if ($web_type == 'admin') {
        if ($cat_array) {
            if ($array_type == 0) {
                $options = array_diff_key($options, $cat_array);
            } else {
                $options = array_intersect_key($options, $cat_array);
            }
        }

        return $options;
    } elseif ($web_type == 'goodsInfo' && $ru_id == 0) {
        $options = array_diff_key($options, $cat_array);
        return $options;
    } else {
        return $options;
    }
}

//ecmoban模板堂 --zhuo end

/**
 *
 * 退货原因列表
 * @staticvar null $res     by  Leah
 * @param type $cause_id 自增id
 * @param type $re_type 返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param type $level 限定返回的级数。为0时返回所有级数
 * @param type $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return string
 */
function cause_list($cause_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
    static $res = null;

    if ($res === null) {
        $res = ReturnCause::whereRaw(1)
            ->orderByRaw("parent_id, sort_order ASC");

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {
                $has_children = ReturnCause::where('parent_id', $row['cause_id'])->count();
                $res[$key]['has_children'] = $has_children;
            }
        }

        //如果数组过大，不采用静态缓存方式
        if (count($res) <= 1000) {
            write_static_cache('cause_pid_releate', $res);
        }
    }

    if (empty($res) == true) {
        return $re_type ? '' : [];
    }

    $options = cause_options($cause_id, $res); // 获得指定分类下的子分类的数组

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
        if ($cause_id == 0) {
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
            $select .= '<option value="' . $var['cause_id'] . '" ';
            $select .= ($selected == $var['cause_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
        }

        return $select;
    } else {
        foreach ($options as $key => $value) {
            $options[$key]['url'] = app(DscRepository::class)->buildUri('reutrn_cause', ['cid' => $value['cause_id']], $value['cause_name']);
        }

        return $options;
    }
}

/**
 * 获取顶部退换货原因
 */
function get_parent_cause()
{
    $result = ReturnCause::where('parent_id', 0)
        ->where('is_show', 1)
        ->orderBy('sort_order');

    $result = BaseRepository::getToArrayGet($result);

    $select = '';
    if ($result) {
        foreach ($result as $var) {
            $select .= '<option value="' . $var['cause_id'] . '" ';
            $select .= '>';
            if (isset($var['level']) && $var['level'] > 0) {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cause_name']), ENT_QUOTES) . '</option>';
        }
    }

    return $select;
}

/**
 * by Leah
 * @staticvar array $cat_options
 * @param type $spec_cat_id
 * @param type $arr
 * @return array
 */
function cause_options($spec_cat_id, $arr)
{
    static $cat_options = [];

    if (isset($cat_options[$spec_cat_id])) {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0])) {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = [];
        //$data = read_static_cache('cause_option_static');
        //$data = [];
//        if ($data === false)
//        {
        while (!empty($arr)) {
            foreach ($arr as $key => $value) {
                $cat_id = $value['cause_id'];
                if ($level == 0 && $last_cat_id == 0) {
                    if ($value['parent_id'] > 0) {
                        break;
                    }

                    $options[$cat_id] = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id'] = $cat_id;
                    $options[$cat_id]['name'] = $value['cause_name'];
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
                    $options[$cat_id]['name'] = $value['cause_name'];
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
            // write_static_cache('cause_option_static', $options);
        }
//        }
//        else
//        {
//            $options = $data;
//        }
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
            if (($spec_cat_id_level == $value['level'] && $value['cause_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level'])
            ) {
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
 * 取出单个晒单图片
 *
 * @param $goods_id
 * @param $order_id
 * @return array
 */
function get_single($goods_id, $order_id)
{
    $singles = Single::where('goods_id', $goods_id)
        ->where('order_id', $order_id)
        ->where('is_audit', 1);

    $singles = BaseRepository::getToArrayFirst($singles);

    $images = [];
    if ($singles) {
        $where = [
            'single_id' => $singles['single_id']
        ];
        $images = app(GoodsGalleryService::class)->getGalleryList($where);
    }

    return $images;
}


/**
 * 取出单个晒单信息
 *
 * @param int $goods_id
 * @param int $order_id
 * @return []
 */
function get_single_detaile($goods_id, $order_id = 0)
{
    $singles = Single::where('goods_id', $goods_id)
        ->where('is_audit', 1);

    if (!empty($order_id)) {
        $singles = $singles->where('order_id', $order_id);
    }

    $singles = $singles->orderBy('addtime');

    $singles = BaseRepository::getToArrayFirst($singles);

    if ($singles) {
        $singles['comment_nums'] = Comment::where('single_id', $singles['single_id'])->count();
        $singles['addtime'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $singles['addtime']);
    }

    return $singles;
}

/**
 * 对二维数组排序
 *
 * @param array([]) $arr
 * @param key $keys
 * @param ASC | DESC $type
 * @return $new_array array([])
 *
 * @author guan
 */
function dimensional_array_sort($arr, $keys, $type = 'DESC')
{
    $keysvalue = $new_array = [];
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'ASC') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

//店铺品牌列表
function get_shop_brand_list($user_id = 0)
{
    $seller_brand = read_static_cache('seller_brand_' . $user_id);

    //将数据写入缓存文件 by wang
    if ($seller_brand === false) {
        $seller_brand = MerchantsShopBrand::select(['bid', 'brandName'])
            ->where('user_id', $user_id)->where('audit_status', 1);

        $seller_brand = $seller_brand->whereHasIn('getLinkBrand', function ($query) {
            $query->whereHasIn('getBrand', function ($query) {
                $query->where('is_show', 1);
            });
        });

        $seller_brand = $seller_brand->with([
            'getLinkBrand' => function ($query) {
                $query->with('getBrand');
            }
        ]);

        $seller_brand = BaseRepository::getToArrayGet($seller_brand);

        if ($seller_brand) {
            foreach ($seller_brand as $key => $row) {
                $brand = $row['get_link_brand']['get_brand'];

                $row = $brand ? array_merge($row, $brand) : $row;

                $seller_brand[$key] = $row;
            }
        }

        write_static_cache('seller_brand_' . $user_id, $seller_brand);
    }

    return $seller_brand;
}

//商家所在位置
function get_shop_address_info($user_id = 0)
{
    $res = get_shop_info_content($user_id);
    $province = get_shop_address($res['province']);
    $city = get_shop_address($res['city']);
    $region = $province . str_repeat("&nbsp;", 2) . $city;

    return $region;
}

function get_shop_address($region, $type = 0)
{
    if ($type == 1) {
        //$region = str_replace(['省', '市'], '', $region);

        return Region::where('region_name', $region)->value('region_id');
    } else {
        return Region::where('region_id', $region)->value('region_name');
    }
}

//店铺信息
function get_shop_info_content($user_id = 0)
{
    $basic_info = SellerShopinfo::where('ru_id', $user_id);
    $basic_info = BaseRepository::getToArrayFirst($basic_info);

    $chat = app(DscRepository::class)->chatQq($basic_info);
    $basic_info['kf_ww'] = $chat['kf_ww'];
    $basic_info['kf_qq'] = $chat['kf_qq'];

    return $basic_info;
}

//店铺搜索 end

/**
 * 指定储值卡使用详情
 *
 * @param int $vc_id 储值卡编号
 * @return array
 */
function value_card_use_info($vc_id = 0)
{
    $res = ValueCardRecord::where('vc_id', $vc_id);
    $res = $res->orderBy('rid', 'desc');

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {

        $order_id = BaseRepository::getKeyPluck($res, 'order_id');
        $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'main_order_id', 'order_sn', 'main_count']);
        foreach ($res as $key => $row) {

            $order = $orderList[$row['order_id']] ?? [];

            $child_list = [];
            $main_count = $order['main_count'] ?? 0;
            if ($main_count > 0) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'main_order_id',
                            'value' => $row['order_id']
                        ],
                        [
                            'name' => 'main_count',
                            'value' => 0
                        ]
                    ]
                ];
                $child_list = BaseRepository::getArraySqlGet($orderList, $sql);
            }

            if ($row['use_val'] > 0 && $row['add_val'] > 0) {
                $row['add_val'] = 0;
                $arr[$key]['use_val'] = $row['use_val'] > 0 ? '+' . app(DscRepository::class)->getPriceFormat($row['use_val']) : app(DscRepository::class)->getPriceFormat($row['use_val']);
            } else {
                $arr[$key]['use_val'] = $row['use_val'] > 0 ? '-' . app(DscRepository::class)->getPriceFormat($row['use_val']) : app(DscRepository::class)->getPriceFormat($row['use_val']);
            }
            $arr[$key]['rid'] = $row['rid'];
            $arr[$key]['order_sn'] = $order['order_sn'] ?? '';
            $arr[$key]['add_val'] = $row['add_val'] > 0 ? '+' . app(DscRepository::class)->getPriceFormat($row['add_val']) : app(DscRepository::class)->getPriceFormat($row['add_val']);
            $arr[$key]['record_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $row['record_time']);

            if (!empty($child_list)) {
                unset($arr[$key]);
            }
        }
    }

    return $arr;
}

//入驻查询品牌
function get_merchants_search_brand($val = '', $type = 0, $brand_type = '', $brand_name = '', $brand_letter = '')
{
    $sqltype = '';
    $arr = [];
    $res = [];
    if (!empty($val) || ($type == 2 && (!empty($brand_name) && !empty($brand_letter)))) {
        if ($type == 2 || $type == 3) {
            if ($brand_type == 'm_bran') {
                $res = MerchantsShopBrand::select('bid as brand_id', 'brandName as brand_name', 'bank_name_letter as brand_letter')
                    ->where('bid', $val)->where('audit_status', 1);

                $res = BaseRepository::getToArrayFirst($res);
            } else {
                $res = Brand::whereRaw(1);

                if ($type == 2) {
                    if (empty($val)) {
                        if (!empty($brand_name)) {
                            $res = $res->where('brand_name', $brand_name);
                        } else {
                            $res = $res->where('brand_letter', $brand_letter);
                        }
                    } else {
                        $res = $res->where('brand_id', $val);
                    }

                    $res = BaseRepository::getToArrayFirst($res);
                } else {
                    $res = BaseRepository::getToArrayGet($res);
                }
            }
        } else {
            $res = Brand::whereRaw(1);

            if ($type == 1) {
                $res = $res->where('brand_letter', 'like', '%' . $val . '%');
            } else {
                $res = $res->where('brand_name', 'like', '%' . $val . '%');
            }

            $res = BaseRepository::getToArrayGet($res);
        }
    }

    return $res;
}

/**
 * 获取品牌数据
 *
 * @param int $brand_id
 * @param int $type
 * @param int $sqlType
 * @return array
 */
function get_link_brand_list($brand_id = 0, $type = 0, $sqlType = 0)
{
    $res = [];
    if ($type > 0) {
        if ($type == 1) { //商家品牌
            $res = LinkBrand::select('bid')
                ->where('bid', $brand_id)
                ->whereHasIn('getMerchantsShopBrand');
            $res = $res->with([
                'getMerchantsShopBrand' => function ($query) {
                    $query->select('bid as brand_id', 'brandName as brand_name');
                }
            ]);
        } elseif ($type == 2 || $type == 4) { //自营品牌
            $res = LinkBrand::select('brand_id')
                ->where('brand_id', $brand_id)
                ->whereHasIn('getBrand');
            $res = $res->with('getBrand');
        } elseif ($type == 3) { //取出关联品牌数据 商家品牌ID
            $res = LinkBrand::select('brand_id')
                ->where('bid', $brand_id)
                ->whereHasIn('getBrand');
            $res = $res->with('getBrand');
        }

        if (isset($res) && $sqlType == 1) {
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $row = isset($row['get_merchants_shop_brand']) && $row['get_merchants_shop_brand'] ? array_merge($row, $row['get_merchants_shop_brand']) : $row;
                    $row = isset($row['get_brand']) && $row['get_brand'] ? array_merge($row, $row['get_brand']) : $row;

                    $res[$key] = $row;
                }
            }
        } elseif (isset($res)) {
            $res = BaseRepository::getToArrayFirst($res);

            $res = isset($res['get_merchants_shop_brand']) && $res['get_merchants_shop_brand'] ? array_merge($res, $res['get_merchants_shop_brand']) : $res;
            $res = isset($res['get_brand']) && $res['get_brand'] ? array_merge($res, $res['get_brand']) : $res;
        }
    }

    return $res;
}

//商品连接地址
function get_return_goods_url($goods_id = 0, $goods_name = '')
{
    if (empty($goods_name)) {
        $goods_name = Goods::where('goods_id', $goods_id)->value('goods_name');
    }

    $url = app(DscRepository::class)->buildUri('goods', ['gid' => $goods_id], $goods_name);
    return $url;
}

//分类地址
function get_return_category_url($cat_id = 0)
{
    $cat_name = Category::where('cat_id', $cat_id)->value('cat_name');
    $url = app(DscRepository::class)->buildUri('category', ['cid' => $cat_id], $cat_name);
    return $url;
}

//店铺商品列表地址
function get_return_store_shop_url($ru_id = 0, $shop_name = '')
{
    if (empty($shop_name)) {
        $shop_name = app(MerchantCommonService::class)->getShopName($ru_id, 1);
    }

    $url = app(DscRepository::class)->buildUri('merchants_store_shop', ['urid' => $ru_id], $shop_name);
    return $url;
}

//店铺地址
function get_return_store_url($params = '', $append = '')
{
    $url = app(DscRepository::class)->buildUri('merchants_store', $params, $append);
    return $url;
}

//搜索地址
function get_return_search_url($keywords = '')
{
    $url = app(DscRepository::class)->buildUri('search', ['chkw' => $keywords], $keywords);
    return $url;
}

function get_return_self_url()
{
    $cur_url = request()->server('PHP_SELF') . "?" . request()->server('QUERY_STRING');
    $cur_url = explode('/', $cur_url);
    $cur_url = $cur_url[count($cur_url) - 1];

    return $cur_url;
}

//导航右边查询分类树 end

function get_template_js($arr = [])
{
    $str = '';
    if ($arr) {
        foreach ($arr as $row) {
            $str .= '<script type="text/javascript" src="' . asset('themes/') . $GLOBALS['_CFG']['template'] . '/js/' . $row . '.js"></script> ';
        }
    }

    return $str;
}

/*
 * 平台分类
 * 获取上下级分类列表 by wu
 * $cat_id      分类id
 * $relation    关系 0:自己 1:上级 2:下级
 * $self        是否包含自己 true:包含 false:不包含
 */
function get_select_category($cat_id = 0, $relation = 0, $self = true, $user_id = 0, $table = 'category')
{
    //静态数组
    static $cat_list = [];
    $cat_list[] = intval($cat_id);

    if ($relation == 0) {
        return $cat_list;
    } elseif ($relation == 1) {
        if ($table == 'merchants_category') {
            $res = MerchantsCategory::whereRaw(1);
        } elseif ($table == 'zc_category') {
            $res = ZcCategory::whereRaw(1);
        } elseif ($table == 'goods_lib_cat') {
            $res = GoodsLibCat::whereRaw(1);
        } elseif ($table == 'wholesale_cat') {
            if (!file_exists(SUPPLIERS)) {
                return [];
            }
            $res = \App\Modules\Suppliers\Models\WholesaleCat::whereRaw(1);
        } elseif ($table == 'presale_cat') {
            $res = PresaleCat::whereRaw(1);
        } else {
            $res = Category::whereRaw(1);
        }

        $parent_id = $res->where('cat_id', $cat_id)->value('parent_id');

        if (!empty($parent_id)) {
            get_select_category($parent_id, $relation, $self, $user_id, $table);
        }

        //删除自己
        if ($self == false) {
            unset($cat_list[0]);
        }

        $cat_list[] = 0;
        //去掉重复，主要是0
        return array_reverse(array_unique($cat_list));
    } elseif ($relation == 2) {
        if ($table == 'merchants_category') {
            $res = MerchantsCategory::whereRaw(1);
        } elseif ($table == 'zc_category') {
            $res = ZcCategory::whereRaw(1);
        } elseif ($table == 'goods_lib_cat') {
            $res = GoodsLibCat::whereRaw(1);
        } elseif ($table == 'wholesale_cat') {
            if (!file_exists(SUPPLIERS)) {
                return [];
            }
            $res = \App\Modules\Suppliers\Models\WholesaleCat::whereRaw(1);
        } elseif ($table == 'presale_cat') {
            $res = PresaleCat::whereRaw(1);
        } else {
            $res = Category::whereRaw(1);
        }

        $res = $res->select('cat_id')
            ->where('cat_id', $cat_id);

        $res = BaseRepository::getToArrayGet($res);

        $child_id = $res ? collect($res)->pluck('cat_id')->all() : [];

        if (!empty($child_id)) {
            foreach ($child_id as $key => $val) {
                get_select_category($val, $relation, $self, $user_id, $table);
            }
        }

        //删除自己
        if ($self == false) {
            unset($cat_list[0]);
        }
        return $cat_list;
    }
}

//获取商家分类 by wu
function get_merchant_category($cat_id = 0, $ru_id = 0)
{
    $res = MerchantsCategory::where('parent_id', $cat_id)
        ->where('user_id', $ru_id)
        ->orderByRaw("sort_order, cat_id desc");

    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

//平台分类--调用下级分类列表 by wu
function insert_select_category($cat_id = 0, $child_cat_id = 0, $cat_level = 0, $select_jsId = 'cat_parent_id', $type = 0, $table = 'category', $seller_shop_cat = [])
{
    $cat_level = $cat_level + 1;
    //获取下级分类列表
    $child_category = app(CategoryService::class)->catList($cat_id, 0, 0, $table, $seller_shop_cat, $cat_level);

    $GLOBALS['smarty']->assign('child_category', $child_category);

    //下级选中分类
    $GLOBALS['smarty']->assign('child_cat_id', $child_cat_id);

    //下级分类等级
    $GLOBALS['smarty']->assign('cat_level', $cat_level);

    //匹配js id
    $GLOBALS['smarty']->assign('select_jsId', $select_jsId);

    //输出类型 0:输出分类id和分类等级 1:只输出分类id 2:只输出分类等级
    $GLOBALS['smarty']->assign('type', $type);

    $html = $GLOBALS['smarty']->fetch('library/get_select_category.lbi');

    return $html;
}

//商家分类--调用下级分类列表 by wu
function insert_seller_select_category($cat_id = 0, $child_cat_id = 0, $cat_level = 0, $select_jsId = 'cat_parent_id', $type = 0, $table = 'category', $seller_shop_cat = [], $user_id = 0)
{

    //获取下级分类列表
    $child_category = app(CategoryService::class)->catList($cat_id, 0, 0, $table, $seller_shop_cat, 0, $user_id);
    $GLOBALS['smarty']->assign('child_category', $child_category);

    //下级选中分类
    $GLOBALS['smarty']->assign('child_cat_id', $child_cat_id);

    //下级分类等级
    $GLOBALS['smarty']->assign('cat_level', $cat_level + 1);

    //匹配js id
    $GLOBALS['smarty']->assign('select_jsId', $select_jsId);

    //输出类型 0:输出分类id和分类等级 1:只输出分类id 2:只输出分类等级
    $GLOBALS['smarty']->assign('type', $type);

    $html = $GLOBALS['smarty']->fetch('library/get_select_category_seller.lbi');

    return $html;
}

//商家入驻分类
function get_seller_mainshop_cat($ru_id)
{
    $user_shopMain_category = MerchantsShopInformation::where('user_id', $ru_id)->value('user_shop_main_category');
    return $user_shopMain_category;
}

//超值礼品包商品的重量和数量
function get_package_goods_info($package_list = [])
{
    if ($package_list) {
        $arr = [];
        $arr['goods_weight'] = 0;

        foreach ($package_list as $key => $row) {
            $arr[$key]['goods_weight'] = $row['goods_number'] * $row['goods_weight'];
            $arr['goods_weight'] += $arr[$key]['goods_weight'];
        }

        return $arr;
    }
}

/**
 * 处理用户名截取字符串
 *
 * @param string $user_name
 * @return mixed
 */
function setAnonymous($user_name = '')
{
    $str = StrRepository::stringToStar($user_name, 1, 3);
    return $str;
}

/*过期申请失效处理  by kong grade*/
function get_invalid_apply($type = 0)
{
    $grade_apply_time = 1;
    if ($GLOBALS['_CFG']['grade_apply_time'] > 0) {
        $grade_apply_time = $GLOBALS['_CFG']['grade_apply_time'];
    }

    $time = TimeRepository::getGmTime() - 24 * 60 * 60 * $grade_apply_time;
    if ($type == 1) {
        $res = SellerTemplateApply::where('pay_status', 0)
            ->where('apply_status', 0)
            ->where('add_time', '<', $time)
            ->delete();
    } else {
        $res = SellerApplyInfo::where('apply_status', 3)
            ->where('is_paid', 0)
            ->where('add_time', '<', $time)
            ->update(['apply_status' => 3]);
    }

    return $res;
}

/*获取商家等级*/
function get_seller_grade($ru_id = 0, $type = 0)
{
    if ($type) {
        $ru_id = $ru_id && !is_array($ru_id) ? explode(",", $ru_id) : $ru_id;
        $res = SellerGrade::whereRaw(1);

        $res = $res->whereHasIn('getMerchantsGrade', function ($query) use ($ru_id) {
            $query->whereIn('ru_id', $ru_id);
        });

        $res = BaseRepository::getToArrayGet($res);

        $str = 1;
        if ($res) {
            foreach ($res as $k => $v) {
                $res[$k]['grade_img'] = app(DscRepository::class)->getImagePath($v['grade_img']);
                if ($v['white_bar'] == 0) {
                    $str = 0;
                    break;
                }
            }
        }

        return $str;
    } else {
        $res = SellerGrade::whereRaw(1);

        $res = $res->whereHasIn('getMerchantsGrade', function ($query) use ($ru_id) {
            $query->where('ru_id', $ru_id);
        });

        $res = $res->with([
            'getMerchantsGrade' => function ($query) use ($ru_id) {
                $query->where('ru_id', $ru_id);
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            $res['grade_img'] = isset($res['grade_img']) && !empty($res['grade_img']) ? app(DscRepository::class)->getImagePath($res['grade_img']) : '';

            $res['add_time'] = $res['get_merchants_grade']['add_time'] ?? '';
            $res['year_num'] = $res['get_merchants_grade']['year_num'] ?? '';
            $res['amount'] = $res['get_merchants_grade']['amount'] ?? '';

            $res['grade_id'] = $res['id'];
        }

        return $res;
    }
}

/*等级到期处理*/
function grade_expire()
{
    $time = TimeRepository::getGmTime();

    //获取默认商家等级id
    $grade_id = SellerGrade::where('is_default', 1)->value('id');

    //存在默认等级  重置到期等级为默认等级  否则删除该商家等级
    if ($grade_id > 0) {
        $other = [
            'grade_id' => $grade_id,
            'add_time' => $time,
            'year_num' => 1
        ];
        $res = MerchantsGrade::whereRaw("add_time + 365 * 24 * 60 * 60 * year_num < $time")
            ->update($other);
    } else {
        $res = MerchantsGrade::whereRaw("add_time + 365 * 24 * 60 * 60 * year_num < $time")->delete();
    }

    return $res;
}


//付款更新众筹信息 by wu
function update_zc_project($order_id = 0)
{
    //取得订单信息
    $order_info = DB::table('order_info')->where('order_id', $order_id)->select('user_id', 'is_zc_order', 'zc_goods_id')->first();
    $order_info = $order_info ? collect($order_info)->toArray() : [];

    $user_id = $order_info ? $order_info['user_id'] : 0;
    $is_zc_order = $order_info ? $order_info['is_zc_order'] : 0;
    $zc_goods_id = $order_info ? $order_info['zc_goods_id'] : 0;

    if ($user_id > 0 && $is_zc_order == 1 && $zc_goods_id > 0) {
        //获取众筹商品信息
        $zc_goods_info = ZcGoods::where('id', $zc_goods_id);
        $zc_goods_info = BaseRepository::getToArrayFirst($zc_goods_info);

        $pid = $zc_goods_info ? $zc_goods_info['pid'] : 0;
        $goods_price = $zc_goods_info ? $zc_goods_info['price'] : 0;

        //增加众筹商品支持的用户id
        $backer_list = $zc_goods_info ? $zc_goods_info['backer_list'] : '';
        if (empty($backer_list)) {
            $backer_list = $user_id;
        } else {
            $backer_list = $backer_list . ',' . $user_id;
        }

        ZcGoods::where('id', $zc_goods_id)->increment('backer_num', 1, ['backer_list' => $backer_list]);

        //增加众筹项目的支持用户总数量、增加众筹项目总金额
        $other = [
            'join_money' => DB::raw("join_money  + ('$goods_price')")
        ];

        ZcProject::where('id', $pid)->increment('join_num', 1, $other);
    }
}

//判断是否有上传文件 by wu
function have_file_upload()
{
    if (!empty($_FILES) && count($_FILES) > 0) {
        foreach ($_FILES as $key => $val) {
            if (empty($val['name'])) {
                unset($_FILES[$key]);
            }
        }
        if (!empty($_FILES) && count($_FILES) > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

//获取众筹商品信息 by wu
function get_zc_goods_info($order_id = 0)
{
    $zc_goods_id = DB::table('order_info')->where('order_id', $order_id)->value('zc_goods_id');
    $zc_goods_id = $zc_goods_id ?? 0;
    if ($zc_goods_id > 0) {
        $res = ZcGoods::where('id', $zc_goods_id);
        $res = $res->with('getZcProject');

        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            $res['gid'] = $res['id'];
            $res = $res['get_zc_project'] ? array_merge($res, $res['get_zc_project']) : $res;

            $res['start_time'] = TimeRepository::getLocalDate('Y-m-d', $res['start_time']);
            $res['end_time'] = TimeRepository::getLocalDate('Y-m-d', $res['end_time']);
            $res['formated_amount'] = app(DscRepository::class)->getPriceFormat($res['amount']);
            $res['formated_price'] = app(DscRepository::class)->getPriceFormat($res['price']);
            $res['formated_shipping_fee'] = app(DscRepository::class)->getPriceFormat($res['shipping_fee']);
            $res['return_time'] = sprintf($GLOBALS['_LANG']['zc_return_detail'], $res['return_time']);
            $res['title_img'] = app(DscRepository::class)->getImagePath($res['title_img']);
        }

        return $res;
    }
    return [];
}

//查询插件权限
function get_user_action_list($admin_id = 0, $string = '')
{
    $action_list = AdminUser::where('user_id', $admin_id)->value('action_list');
    return $action_list;
}

//查询插件权限
function get_merchants_permissions($action_list, $string = '')
{
    if ($action_list == 'all') {
        return 1;
    } else {
        $action_list = explode(',', $action_list);
        if (in_array($string, $action_list)) {
            return 1;
        } else {
            return 0;
        }
    }
}

/*
 * 平台分类
 * 获取当级分类列表 by wu
 * $cat_id      分类id
 * $relation    关系 0:自己 1:上级 2:下级
 */
function get_category_list($cat_id = 0, $relation = 0, $seller_shop_cat = [], $user_id = 0, $for_level = 0, $table = 'category')
{
    $parent_id = 0;
    if ($relation == 0 || $relation == 1) {
        if ($table == 'category') {
            $parent_id = Category::where('cat_id', $cat_id)->value('parent_id');
        } elseif ($table == 'zc_category') {
            $parent_id = ZcCategory::where('cat_id', $cat_id)->value('parent_id');
        } elseif ($table == 'goods_lib_cat') {
            $parent_id = GoodsLibCat::where('cat_id', $cat_id)->value('parent_id');
        } elseif ($table == 'wholesale_cat') {
            if (!file_exists(SUPPLIERS)) {
                return [];
            }
            $parent_id = \App\Modules\Suppliers\Models\WholesaleCat::where('cat_id', $cat_id)->value('parent_id');
        } elseif ($table == 'presale_cat') {
            $parent_id = PresaleCat::where('cat_id', $cat_id)->value('parent_id');
        } else {
            $parent_id = MerchantsCategory::where('cat_id', $cat_id)->value('parent_id');
        }
    } elseif ($relation == 2) {
        $parent_id = $cat_id;
    }

    $parent_id = $parent_id ? $parent_id : 0;

    if ($table == 'category') {
        $category_list = Category::where('parent_id', $parent_id);
    } elseif ($table == 'zc_category') {
        $category_list = ZcCategory::where('parent_id', $parent_id);
    } elseif ($table == 'goods_lib_cat') {
        $category_list = GoodsLibCat::where('parent_id', $parent_id);
    } elseif ($table == 'wholesale_cat') {
        if (!file_exists(SUPPLIERS)) {
            return [];
        }
        $category_list = \App\Modules\Suppliers\Models\WholesaleCat::where('parent_id', $parent_id);
    } elseif ($table == 'presale_cat') {
        $category_list = PresaleCat::where('parent_id', $parent_id);
    } else {
        $category_list = MerchantsCategory::where('parent_id', $parent_id);
    }

    if ($user_id) {
        if (isset($seller_shop_cat['parent']) && $for_level < 3) {
            $seller_shop_cat['parent'] = app(DscRepository::class)->delStrComma($seller_shop_cat['parent']);
            $parent = !is_array($seller_shop_cat['parent']) ? explode(",", $seller_shop_cat['parent']) : $seller_shop_cat['parent'];
            $category_list = $category_list->whereIn('cat_id', $parent);
        }
    }

    $category_list = $category_list->where('is_show', 1);

    if ($table == 'category') {
        $category_list = $category_list->orderBy('sort_order', 'ASC');
    }

    $category_list = BaseRepository::getToArrayGet($category_list);

    if ($category_list) {
        foreach ($category_list as $key => $val) {
            if ($cat_id == $val['cat_id']) {
                $is_selected = 1;
            } else {
                $is_selected = 0;
            }
            $category_list[$key]['is_selected'] = $is_selected;

            $category_list[$key]['url'] = app(DscRepository::class)->buildUri($table, ['cid' => $val['cat_id']], $val['cat_name']);
        }
    }

    return $category_list;
}

/**
 * 搜索品牌列表
 *
 * @param int $goods_id
 * @param null $ru_id
 * @return array|bool
 */
function search_brand_list($goods_id = 0, $ru_id = null)
{
    if (!is_null($ru_id)) {
        $seller_id = $ru_id;
    } else {
        if ($goods_id > 0) {
            $seller_id = Goods::where('goods_id', $goods_id)->value('user_id');
        } else {
            $adminru = get_admin_ru_id();
            $seller_id = $adminru ? $adminru['ru_id'] : 0;
        }
    }

    $letter = !isset($_REQUEST['letter']) && empty($_REQUEST['letter']) ? '' : addslashes(trim($_REQUEST['letter']));
    $keyword = !isset($_REQUEST['keyword']) && empty($_REQUEST['keyword']) ? '' : addslashes(trim($_REQUEST['keyword']));

    /* 读取缓存 */
    $brand_list = BaseRepository::getDiskForeverData('pin_brands');

    if ($brand_list === false && empty($keyword)) {
        $res = Brand::whereRaw(1);

        if (!empty($keyword)) {
            $keyword = mysql_like_quote($keyword);
            $res = $res->where('brand_name', 'like', '%' . $keyword . '%')
                ->orWhere('brand_letter', 'like', '%' . $keyword . '%');
        }

        $res = $res->orderBy('sort_order');

        $res = BaseRepository::getToArrayGet($res);

        $pin = app(Pinyin::class);

        $brand_list = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if ($seller_id) {
                    $val['is_brand'] = get_seller_brand_count($val['brand_id'], $seller_id);
                } else {
                    $val['is_brand'] = 1;
                }

                if ($val['is_brand'] > 0) {
                    $brand_list[$key]['brand_id'] = $val['brand_id'];
                    $brand_list[$key]['brand_name'] = $val['brand_name'];
                    $brand_list[$key]['brand_letter'] = $val['brand_letter'];
                    $brand_list[$key]['letter'] = !empty($val['brand_first_char']) ? $val['brand_first_char'] : strtoupper(substr($pin->Pinyin($val['brand_name'], EC_CHARSET), 0, 1));
                } else {
                    unset($brand_list[$key]);
                }
            }
        }

        !empty($brand_list) ? ksort($brand_list) : [];

        if ($brand_list) {
            /* 存储缓存 */
            BaseRepository::setDiskForever('pin_brands', $brand_list);
        }
    } else {
        if ($brand_list === false) {
            $pin = app(Pinyin::class);
            if (is_array($brand_list)) {
                foreach ($brand_list as $key => $val) {
                    if ($seller_id) {
                        $val['is_brand'] = get_seller_brand_count($val['brand_id'], $seller_id);
                    } else {
                        $val['is_brand'] = 1;
                    }

                    if ($val['is_brand'] > 0) {
                        $brand_list[$key]['brand_id'] = $val['brand_id'];
                        $brand_list[$key]['brand_name'] = $val['brand_name'];
                        $brand_list[$key]['brand_letter'] = $val['brand_letter'];
                        $brand_list[$key]['letter'] = !empty($val['brand_first_char']) ? $val['brand_first_char'] : strtoupper(substr($pin->Pinyin($val['brand_name'], EC_CHARSET), 0, 1));
                    } else {
                        unset($brand_list[$key]);
                    }
                }
            }

            /* 存储缓存 */
            BaseRepository::setDiskForever('pin_brands', $brand_list);
        }

        $arr = [];
        if ($brand_list) {
            foreach ($brand_list as $key => $val) {
                if (!empty($letter) && empty($keyword)) {
                    if ($letter == "QT" && !$brand_list[$key]['letter']) {
                        $arr[$key] = $val;
                    } elseif ($letter == $brand_list[$key]['letter']) {
                        $arr[$key] = $val;
                    }
                } else {
                    $arr = $brand_list;
                }
            }
        }

        if (!empty($keyword)) {
            $arr = ArrRepository::getSearchArray($arr, $keyword, ['brand_name', 'brand_letter']);
        }

        $brand_list = $arr;
    }

    return $brand_list;
}

/**
 * 获取商家关联品牌数量
 *
 * @param int $brand_id
 * @param int $seller_id
 * @return mixed
 */
function get_seller_brand_count($brand_id = 0, $seller_id = 0)
{
    $res = LinkBrand::whereRaw(1)
        ->whereHasIn('getBrand')
        ->whereHasIn('getMerchantsShopBrand', function ($query) use ($seller_id) {
            if ($seller_id) {
                $query->where('user_id', $seller_id);
            }
        });

    if ($brand_id) {
        $res = $res->where('brand_id', $brand_id);
    }

    $count = $res->count();

    return $count;
}

/**
 * 取得可用的配送方式列表
 * @param array $region_id_list 收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 */
function available_shipping_list($region, $info = [], $is_limit = 0)
{
    $info['freight'] = $info['freight'] ?? 1;

    $info['tid'] = $info['tid'] ?? [-1];
    $info['tid'] = BaseRepository::getExplode($info['tid']);

    $info['ru_id'] = $info['ru_id'] ?? 0;

    $shipping_list = [];
    $shipping_list1 = [];
    $shipping_list2 = [];

    if ($region) {
        if ($info['freight'] == 2) {
            $freight_type = GoodsTransport::whereIn('tid', $info['tid'])
                ->where('ru_id', $info['ru_id'])
                ->value('freight_type');
            $freight_type = $freight_type ? $freight_type : 0;

            if ($freight_type == 1) {
                $find_in_set = get_find_in_set_field($region, 'region_id');
                $shipping_list1 = GoodsTransportTpl::select('shipping_id')
                    ->whereIn('tid', $info['tid'])
                    ->where('user_id', $info['ru_id'])
                    ->whereRaw($find_in_set);

                $shipping_list1 = $shipping_list1->with([
                    'getShipping' => function ($query) {
                        $query->where('enabled', 1);
                    }
                ]);

                $shipping_list1 = $shipping_list1->groupBy('shipping_id');

                if ($is_limit) {
                    $shipping_list1 = $shipping_list1->take(1);
                }

                $shipping_list1 = BaseRepository::getToArrayGet($shipping_list1);

                if ($shipping_list1) {
                    foreach ($shipping_list1 as $key => $row) {
                        $row = $row['get_shipping'] ? array_merge($row, $row['get_shipping']) : $row;

                        $shipping_list1[$key] = $row;

                        if (empty($row['get_shipping'])) {
                            unset($shipping_list1[$key]);
                        } else {
                            $shipping_list1[$key] = $row;
                        }
                    }
                }

                $shipping_list2 = GoodsTransportExtend::where('ru_id', $info['ru_id'])
                    ->whereIn('tid', $info['tid']);

                $shipping_list2 = $shipping_list2->whereHasIn('getGoodsTransportExpress', function ($query) use ($info) {
                    $query->where('ru_id', $info['ru_id']);
                });

                $find_in_set = get_find_in_set_field($region, 'area_id');
                $shipping_list2 = $shipping_list2->whereRaw($find_in_set);

                $shipping_list2 = $shipping_list2->with(['getGoodsTransportExpress']);

                $shipping_list2 = BaseRepository::getToArrayGet($shipping_list2);

                if ($shipping_list2) {
                    foreach ($shipping_list2 as $gtkey => $gtval) {
                        $gtval = $gtval['get_goods_transport_express'] ? array_merge($gtval, $gtval['get_goods_transport_express']) : $gtval;

                        $shippingInfo = [];
                        if ($gtval['shipping_id']) {
                            $gt_shipping_id = !is_array($gtval['shipping_id']) ? explode(",", $gtval['shipping_id']) : $gtval['shipping_id'];

                            $shippingInfo = Shipping::select('shipping_id', 'shipping_code', 'shipping_name', 'shipping_order')
                                ->where('enabled', 1)
                                ->whereIn('shipping_id', $gt_shipping_id);

                            $shippingInfo = BaseRepository::getToArrayFirst($shippingInfo);

                            $gtval = $shippingInfo ? array_merge($gtval, $shippingInfo) : $gtval;
                        }

                        if (empty($shippingInfo)) {
                            unset($shipping_list2[$gtkey]);
                        } else {
                            $shipping_list2[$gtkey] = $gtval;
                        }
                    }
                }
            } else {
                /* 运费模板配送方式 start */
                $goods_ship_transport = GoodsTransportExpress::where('ru_id', $info['ru_id'])
                    ->whereIn('tid', $info['tid']);

                $goods_ship_transport = BaseRepository::getToArrayGet($goods_ship_transport);
                $shipping_id = BaseRepository::getKeyPluck($goods_ship_transport, 'shipping_id');

                $list = [];
                if ($shipping_id) {
                    foreach ($shipping_id as $key => $val) {
                        $list[] = $val ? explode(',', $val) : [];
                    }
                }

                $shipping_id = BaseRepository::getFlatten($list);
                $shipping_id = $shipping_id ? array_unique($shipping_id) : [];
                $shipping_id = BaseRepository::getSort($shipping_id);
                /* 运费模板配送方式 end */

                if ($shipping_id) {
                    $shipping_list1 = Shipping::whereIn('shipping_id', $shipping_id);
                    $shipping_list1 = BaseRepository::getToArrayGet($shipping_list1);
                } else {
                    $shipping_list1 = [];
                }
            }
        } elseif ($info['freight'] == 1) {
            // 固定运费 调用默认配送方式
            $shipping_id = SellerShopinfo::where('ru_id', $info['ru_id'])->value('shipping_id');
            $shipping_id = $shipping_id ?? 0;

            $shipping = Shipping::where('shipping_id', $shipping_id);
            $shipping_list1 = BaseRepository::getToArrayGet($shipping);
        }
    }

    if ($shipping_list1 && $shipping_list2) {
        $shipping_list = array_merge($shipping_list1, $shipping_list2);
    } elseif ($shipping_list1) {
        $shipping_list = $shipping_list1;
    } elseif ($shipping_list2) {
        $shipping_list = $shipping_list2;
    }

    if ($shipping_list) {
        //去掉重复配送方式 start
        $new_shipping = [];
        foreach ($shipping_list as $key => $val) {
            @$new_shipping[$val['shipping_code']][] = $key;
        }

        foreach ($new_shipping as $key => $val) {
            if (count($val) > 1) {
                for ($i = 1; $i < count($val); $i++) {
                    unset($shipping_list[$val[$i]]);
                }
            }
        }
        //去掉重复配送方式 end

        $shipping_list = get_array_sort($shipping_list, 'shipping_order');
    }

    $cfg = [
        ['name' => 'item_fee', 'value' => 0],
        ['name' => 'base_fee', 'value' => 0],
        ['name' => 'step_fee', 'value' => 0],
        ['name' => 'free_money', 'value' => 100000],
        ['name' => 'step_fee1', 'value' => 0],
        ['name' => 'pack_fee', 'value' => 0],
    ];

    if ($shipping_list) {
        foreach ($shipping_list as $key => $row) {
            if (!isset($row['configure']) && empty($row['configure'])) {
                $shipping_list[$key]['configure'] = serialize($cfg);
            }
        }
    }

    return $shipping_list;
}

function get_find_in_set_field($region, $field)
{
    $find_in_set = '(';
    foreach ($region as $key => $val) {
        if ($key == 0) {
            $find_in_set .= "FIND_IN_SET('" . $region[$key] . "', $field)";
        } else {
            $find_in_set .= " OR FIND_IN_SET('" . $region[$key] . "', $field)";
        }
    }
    $find_in_set .= ')';

    return $find_in_set;
}

/* 返回地址 by wu */
function get_complete_address($info = [])
{
    $complete_address = [];
    if (isset($info['country']) && $info['country']) {
        $region_info = Region::select('region_id', 'region_name', 'parent_id')->where('region_id', $info['country']);
        $region_info = BaseRepository::getToArrayFirst($region_info);

        $complete_address[] = isset($region_info['region_name']) ? $region_info['region_name'] : '';
    }
    if (isset($info['province']) && $info['province']) {
        $region_info = Region::select('region_id', 'region_name', 'parent_id')->where('region_id', $info['province']);
        $region_info = BaseRepository::getToArrayFirst($region_info);

        $complete_address[] = isset($region_info['region_name']) ? $region_info['region_name'] : '';
    }
    if (isset($info['city']) && $info['city']) {
        $region_info = Region::select('region_id', 'region_name', 'parent_id')->where('region_id', $info['city']);
        $region_info = BaseRepository::getToArrayFirst($region_info);

        $complete_address[] = isset($region_info['region_name']) ? $region_info['region_name'] : '';
    }
    if (isset($info['district']) && $info['district']) {
        $region_info = Region::select('region_id', 'region_name', 'parent_id')->where('region_id', $info['district']);
        $region_info = BaseRepository::getToArrayFirst($region_info);

        $complete_address[] = isset($region_info['region_name']) ? $region_info['region_name'] : '';
    }

    $complete_address = !empty($complete_address) ? implode(' ', $complete_address) : '';
    return $complete_address;
}

/* 获取记录信息 by wu */
function get_store_order_info($id = 0, $type = 'id')
{
    $res = StoreOrder::whereRaw(1);

    if ($type == 'id') {
        $res = $res->where('id', $id);
    }

    if ($type == 'order_id') {
        $res = $res->where('order_id', $id);
    }

    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/* 获取商家门店列表 by wu */
function get_store_list($order_id = 0)
{
    $ru_id = get_ru_id($order_id);
    $store_list = OfflineStore::where('ru_id', $ru_id);
    $store_list = BaseRepository::getToArrayGet($store_list);

    if ($store_list) {
        foreach ($store_list as $key => $val) {
            $info = ['country' => $val['country'],
                'province' => $val['province'],
                'city' => $val['city'],
                'district' => $val['district']
            ];
            $store_list[$key]['complete_store_address'] = get_complete_address($info) . ' ' . $val['stores_address'];
        }
    }

    return $store_list;
}

/* 通过订单商品返回ru_id by wu */
function get_ru_id($order_id = 0)
{
    $ru_id = OrderGoods::where('order_id', $order_id)->value('ru_id');

    if (!$ru_id) {
        $adminru = get_admin_ru_id();
        $ru_id = $adminru['ru_id'];
    }

    return $ru_id;
}

/**
 * 重定义商品价格
 * 获取商品属性默认选择中数组
 * end
 */
function get_goods_checked_attr($values)
{
    foreach ($values as $key => $val) {
        if ($val['checked']) {
            return $val;
        }
    }
}

/**
 * 获取活动信息
 */
function get_goods_activity_info($act_id = 0, $select = [])
{
    $activity = GoodsActivity::where('review_status', 3)->where('act_id', $act_id);
    $activity = BaseRepository::getToArrayFirst($activity);

    if ($activity) {
        $activity['goods_thumb'] = !empty($activity['activity_thumb']) ? app(DscRepository::class)->getImagePath($activity['activity_thumb']) : app(DscRepository::class)->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');
    }

    return $activity;
}

/**
 * 易源数据接口（https://www.showapi.com/）
 * 创建参数(包括签名的处理)
 */
function get_showapi()
{
    $paramArr = [
        'showapi_appid' => '29464',  //appid
        'code' => '737110900011' //条形码
    ];

    $showapi_secret = "ad31a785a8614098a4e16227c175145d"; //secret

    $paraStr = "";
    $signStr = "";
    ksort($paramArr);
    foreach ($paramArr as $key => $val) {
        if ($key != '' && $val != '') {
            $signStr .= $key . $val;
            $paraStr .= $key . '=' . urlencode($val) . '&';
        }
    }
    $signStr .= $showapi_secret; //排好序的参数加上secret,进行md5
    $sign = strtolower(md5($signStr));
    $paraStr .= 'showapi_sign=' . $sign; //将md5后的值作为参数,便于服务器的效验

    $http = app(\App\Libraries\Http::class);
    $hres = $http->doPost("http://route.showapi.com/66-22", $paraStr);
    return dsc_decode($hres, true);
}

/* 极速数据扫码接口（http://www.jisuapi.com/） by wu */
function get_jsapi($paramArr = [])
{
    $paraStr = "";
    $signStr = '';
    foreach ($paramArr as $key => $val) {
        if ($key != '' && $val != '') {
            $signStr .= $key . $val;
            $paraStr .= $key . '=' . urlencode($val) . '&';
        }
    }

    $url = "https://api.jisuapi.com/barcode2/query";
    $http = app(\App\Libraries\Http::class);
    $hres = $http->doPost($url, $paraStr);
    return dsc_decode($hres, true);
}

/* 获取扫码配置数据 by wu */
function get_scan_code_config($ru_id = 0)
{
    $config = SellerShopinfo::select('js_appkey', 'js_appsecret')->where('ru_id', $ru_id);
    $config = BaseRepository::getToArrayFirst($config);

    return $config;
}

/* 获取扩展数据 by wu */

function get_goods_extend_info($goods_id = 0)
{
    $extend_info = GoodsExtend::select('width', 'height', 'depth', 'origincountry', 'originplace', 'assemblycountry', 'barcodetype', 'catena', 'isbasicunit', 'packagetype', 'grossweight', 'netweight', 'netcontent', 'licensenum', 'healthpermitnum')
        ->where('goods_id', $goods_id);
    $extend_info = BaseRepository::getToArrayFirst($extend_info);

    $arr = [];
    if ($extend_info) {
        foreach ($extend_info as $key => $val) {
            if (isset($GLOBALS['_LANG'][$key]) && !empty($val)) {
                $arr[$GLOBALS['_LANG'][$key]] = $val;
            }
        }
    }

    return $arr;
}

/**
 * 获取当前位置店铺
 *
 * @param int $ru_id
 * @param int $warehouse_id
 * @param int $area_id
 * @param int $area_city
 * @param int $city_id
 * @param string $spec_arr
 * @param int $goods_id
 * @param int $provinces_id
 * @param int $district_id
 * @param int $type
 * @param int $store_id
 * @param int $limit
 * @return mixed
 */
function get_goods_user_area_position($ru_id = 0, $warehouse_id = 0, $area_id = 0, $area_city = 0, $city_id = 0, $spec_arr = '', $goods_id = 0, $provinces_id = 0, $district_id = 0, $type = 0, $store_id = 0, $limit = 0)
{
    $store_list = OfflineStore::where('is_confirm', 1);

    if ($provinces_id > 0) {
        $store_list = $store_list->where('province', $provinces_id);
    }

    if ($city_id > 0) {
        $store_list = $store_list->where('city', $city_id);
    }

    if ($district_id > 0) {
        $store_list = $store_list->where('district', $district_id);
    }

    if ($store_id > 0) {
        $store_list = $store_list->where('id', $store_id);
    } else {
        $store_list = $store_list->where('ru_id', $ru_id);
    }

    if ($goods_id > 0) {
        $store_list = $store_list->whereHasIn('getStoreGoods', function ($query) use ($goods_id) {
            $query->where('goods_id', $goods_id);
        });

        $store_list = $store_list->with([
            'getStoreGoods' => function ($query) use ($goods_id) {
                $query->select('store_id', 'goods_id', 'goods_number')
                    ->where('goods_id', $goods_id);
            }
        ]);
    }

    if ($limit > 0) {
        $store_list = $store_list->take($limit);
    }

    $store_list = BaseRepository::getToArrayGet($store_list);

    if ($store_list) {
        foreach ($store_list as $key => $row) {
            $row = $row['get_store_goods'] ? array_merge($row, $row['get_store_goods']) : $row;

            $unset_type = 0;
            if ($spec_arr) {
                $products = app(GoodsWarehouseService::class)->getWarehouseAttrNumber($row['goods_id'], $spec_arr, $warehouse_id, $area_id, $area_city, '', $row['id']); //获取属性库存
                $store_list[$key]['goods_number'] = $products ? $products['product_number'] : 0;

                if ($store_list[$key]['goods_number'] == 0) {
                    unset($store_list[$key]);
                    $unset_type = 1;
                }
            } else {
                $store_list[$key]['goods_number'] = $row['goods_number'];
            }

            if ($type == 0 && $unset_type == 0) {
                $region = [
                    'province' => $row['province'],
                    'city' => $row['city'],
                    'district' => $row['district'],
                ];
                $store_list[$key]['area_info'] = get_area_region_info($region);
            }
        }
    }

    if (!empty($store_list)) {
        sort($store_list);
    }
    return $store_list;
}

/* 使用限制条件格式化 */
function condition_format($conditon)
{
    switch ($conditon) {
        case 1:
            return $GLOBALS['_LANG']['spec_cat'];
            break;
        case 2:
            return $GLOBALS['_LANG']['spec_goods'];
            break;
        case 0:
            return $GLOBALS['_LANG']['all_goods'];
        default:
            return 'N/A';
            break;
    }
}

/**
 * 获取上一级地区
 */
function get_parent_regions($region_id = 0)
{
    $parent_id = Region::where('region_id', $region_id)->value('parent_id');

    $res = Region::where('parent_id', $parent_id);
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

/**
 * 清除缓存
 */
function set_clear_cache($dirName = '', $arr = [], $type = 0)
{
    $j = 0;
    if (is_dir($dirName)) {
        if ($handle = opendir($dirName)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != ".." && $item != 'admin' && $item != SELLER_PATH && $item != STORES_PATH && $item != 'index.htm' && $item != 'index.html') {
                    $aaa[] = $item;
                    if (!is_dir("$dirName/$item")) {
                        if ($arr) {
                            if ($type > 0) {
                                $i = 0;
                                foreach ($arr as $k => $v) {
                                    if ($v) {
                                        if (strstr($item, $v)) {
                                            $i++;
                                        }
                                    }
                                }
                                if ($i == 0) {
                                    $j++;
                                    @unlink("$dirName/$item");
                                }
                                for ($i = 0; $i < 16; $i++) {
                                    $hash_dir = storage_path('framework/cache/data') . dechex($i);
                                    $dirs = $hash_dir;
                                    set_clear_cache($dirs);
                                }
                            } else {
                                foreach ($arr as $k => $v) {
                                    if ($v) {
                                        if (strstr($item, $v)) {
                                            $j++;
                                            @unlink("$dirName/$item");
                                        }
                                    }
                                }
                            }
                        } else {
                            $j++;
                            @unlink("$dirName/$item");
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
    return $j;
}

/* 获取分类店铺
 * $cat_id 分类id
 * $num 获取数量
 * 返回店铺列表
 */
function get_category_store($cat_id = 0, $num = 6)
{
    $CategoryRep = app(CategoryService::class);

    if ($cat_id) {
        $children = $CategoryRep->getCatListChildren($cat_id);
    } else {
        $children = [];
    }

    // 获取分类下店铺
    $store_list = Goods::select("user_id, COUNT(*) AS goods_num")
        ->where('is_on_sale', 1)
        ->where('is_alone_sale', 1)
        ->where('is_delete', 0);

    $store_list = CommonRepository::constantMaxId($store_list, 'user_id');

    if ($children) {
        $store_list = $store_list->whereIn('cat_id', $children);
    }

    $store_list = $store_list->with('getSellerShopInfo');

    $store_list = $store_list->groupBy('user_id')
        ->having('goods_num', '>', 0);

    $store_list = $store_list->orderBy('goods_num', 'desc');

    $store_list = $store_list->take($num);

    $store_list = BaseRepository::getToArrayGet($store_list);

    if ($store_list) {
        foreach ($store_list as $key => $row) {
            $row = $row['get_seller_shop_info'] ? array_merge($row, $row['get_seller_shop_info']) : $row;

            $store_list[$key] = $row;

            $build_uri = [
                'urid' => $row['user_id'],
                'append' => isset($row['shop_name']) && !empty($row['shop_name']) ? $row['shop_name'] : ''
            ];

            $domain_url = app(MerchantCommonService::class)->getSellerDomainUrl($row['user_id'], $build_uri);
            $store_list[$key]['shop_url'] = $domain_url['domain_name'];
        }
    }

    return $store_list;
}

/* 获取上传附件大小 by wu
 * type: 0-字节，1-格式化
 */
function upload_size_limit($type = 0)
{
    $upload_size_limit = $GLOBALS['_CFG']['upload_size_limit'] == '-1' ? ini_get('upload_max_filesize') . 'B' : $GLOBALS['_CFG']['upload_size_limit'] . 'KB';
    $upload_size_limit = strtoupper($upload_size_limit);

    if ($type == 0) {
        $size = $upload_size_limit[strlen($upload_size_limit) - 2];
        $upload_size_limit = intval(preg_replace("/(KB|MB)/i", "", $upload_size_limit));
        switch ($size) {
            case 'M':
                $upload_size_limit *= 1024 * 1024;
                break;
            case 'K':
                $upload_size_limit *= 1024;
                break;
        }
    }

    return $upload_size_limit;
}

//导航右边查询分类树 start
function get_top_category_tree($parent_id = 0)
{
    $res = Category::where('parent_id', $parent_id)
        ->where('is_show', 1)
        ->orderBy('sort_order')
        ->orderBy('cat_id');

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $key => $row) {
            $arr[$row['cat_id']]['id'] = $row['cat_id'];
            $arr[$row['cat_id']]['cat_alias_name'] = $row['cat_alias_name'];
            $arr[$row['cat_id']]['url'] = app(DscRepository::class)->buildUri('seckill', ['cid' => $row['cat_id']], $row['cat_name']);
            $arr[$row['cat_id']]['style_icon'] = $row['style_icon']; //分类菜单图标
            $arr[$row['cat_id']]['cat_icon'] = $row['cat_icon']; //自定义图标

            $arr[$row['cat_id']]['nolinkname'] = $row['cat_name'];
        }
    }

    return $arr;
}

// 预售看了又看
function get_top_presale_goods($goods_id, $cat_id)
{
    $now = TimeRepository::getGmTime();

    $res = Goods::where('is_on_sale', 0)->where('goods_id', $goods_id);

    if (config('shop.review_goods') == 1) {
        $res = $res->whereIn('review_status', [3, 4, 5]);
    }

    $activityWhere = [
        'cat_id' => $cat_id,
        'time' => $now,
    ];
    $res = $res->whereHasIn('getPresaleActivity', function ($query) use ($activityWhere) {
        $query->where('cat_id', $activityWhere['cat_id'])
            ->where('start_time', '<=', $activityWhere['time'])
            ->where('end_time', '>=', $activityWhere['time'])
            ->where('review_status', 3);
    });

    $res = $res->with([
        'getPresaleActivity',
        'getSellerShopInfo'
    ]);

    $res = $res->orderBy('click_count', 'desc');

    $res = $res->take(5);

    $res = BaseRepository::getToArrayGet($res);

    if ($res) {
        foreach ($res as $key => $row) {
            $row = $row['get_presale_activity'] ? array_merge($row, $row['get_presale_activity']) : $row;
            $row = $row['get_seller_shop_info'] ? array_merge($row, $row['get_seller_shop_info']) : $row;

            $res[$key]['goods_name'] = $row['goods_name'];
            $res[$key]['shop_price'] = app(DscRepository::class)->getPriceFormat($res[$key]['shop_price']);
            $res[$key]['goods_thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);
            $res[$key]['thumb'] = app(DscRepository::class)->getImagePath($row['goods_thumb']);
            $res[$key]['goods_img'] = app(DscRepository::class)->getImagePath($row['goods_img']);

            $res[$key]['logo_thumb'] = str_replace('../', '', $row['logo_thumb']); //商家缩略图
            $res[$key]['logo_thumb'] = app(DscRepository::class)->getImagePath($res[$key]['logo_thumb']);

            $res[$key]['street_thumb'] = app(DscRepository::class)->getImagePath($row['street_thumb']);
            $res[$key]['brand_thumb'] = app(DscRepository::class)->getImagePath($row['brand_thumb']);
            $res[$key]['url'] = app(DscRepository::class)->buildUri('presale', ['act' => 'view', 'presaleid' => $row['act_id']], $row['goods_name']);
        }
    }

    return $res;
}

/**
 * 创建已付款订单快照信息
 *
 * @param int $order_id
 * @return string
 */
function create_snapshot($order_id = 0)
{
    return \App\Repositories\Order\OrderRepository::create_snapshot($order_id);
}

/**
 * 预售订单增加预售人数
 * @param int $order_id
 * @return bool
 */
function get_presale_num($order_id = 0)
{
    return \App\Repositories\Activity\PresaleRepository::increment_presale_num($order_id);
}

/**
 * 更新商品销量
 *
 * @param int $order_id
 * @param array $order
 * @return bool|int
 */
function get_goods_sale($order_id = 0, $order = [])
{
    // 增加销量时机
    $sales_volume_time = config('shop.sales_volume_time');
    if ($sales_volume_time == SALES_PAY) {
        // 付款更新商品销量
        return \App\Repositories\Order\OrderRepository::increment_goods_sale_pay($order_id, $order);

    } elseif ($sales_volume_time == SALES_SHIP) {
        // 发货更新商品销量
        return \App\Repositories\Order\OrderRepository::increment_goods_sale_ship($order_id, $order);
    }

    return false;
}

/**
 * 减去商品销量 - 后台设置订单未付款、未发货时
 * @param int $order_id
 * @param array $order
 * @return bool|int
 */
function decrement_goods_sale($order_id = 0, $order = [])
{
    $sales_volume_time = config('shop.sales_volume_time');
    if ($sales_volume_time == SALES_PAY) {
        // 设置订单未付款 更新商品销量
        return \App\Repositories\Order\OrderRepository::decrement_goods_sale_unpaid($order_id, $order);

    } elseif ($sales_volume_time == SALES_SHIP) {
        // 设置订单未发货 更新商品销量
        return \App\Repositories\Order\OrderRepository::decrement_goods_sale_unshipped($order_id, $order);
    }

    return false;
}

/**
 * 设置表单提交token
 * @param string $cookie cookie名称
 * @return  void
 */
function set_prevent_token($cookie = '')
{
    if ($cookie) {
        app(SessionRepository::class)->deleteCookie($cookie);

        $sc_rand = rand(1000, 9999);
        $sc_guid = sc_guid();

        $prevent_cookie = MD5($sc_guid . "-" . $sc_rand);
        cookie()->queue($cookie, $prevent_cookie, 30 * 24 * 60);

        $GLOBALS['smarty']->assign('sc_guid', $sc_guid);
        $GLOBALS['smarty']->assign('sc_rand', $sc_rand);
    }
}

/**
 * 检查是否存在主订单 如果子订单均完成付款 改变主订单状态
 *
 * @param int $order_id
 */
function check_main_order_status($order_id = 0)
{
    $main_order_id = OrderInfo::where('order_id', $order_id)->value('main_order_id');

    if ($main_order_id) {
        $order_ids = OrderInfo::where('main_order_id', $main_order_id);
        $order_ids = BaseRepository::getToArrayGet($order_ids);

        $order_status = OS_CONFIRMED;
        $pay_status = PS_PAYED;

        if ($order_ids) {
            foreach ($order_ids as $v) {
                $order_info = OrderInfo::where('order_id', $v['order_id']);
                $order_info = BaseRepository::getToArrayFirst($order_info);

                //有待细化 目前如果有主订单 下面子订单不是已确认 就设置为未确认
                if ($order_info && $order_info['order_status'] != OS_CONFIRMED) {
                    $order_status = OS_UNCONFIRMED;
                }

                //有待细化 目前如果有主订单 下面子订单不是已付款 就设置为未付款
                if ($order_info && $order_info['pay_status'] != PS_PAYED) {
                    $pay_status = PS_UNPAYED;
                }
            }
        }

        $other = [
            'order_status' => $order_status,
            'pay_status' => $pay_status
        ];
        OrderInfo::where('order_id', $main_order_id)->update($other);
    }
}

/* 卖场-获取地区每一级 */
function get_region_level($region_id = 0)
{
    $array = [];
    while ($region_id > 0) {
        $array[] = intval($region_id);
        $region_id = Region::where('region_id', $region_id)->value('parent_id');
    }
    $array = array_reverse($array);

    return $array;
}

/* 卖场-获取卖场列表 */
function get_region_store_list()
{
    $res = RegionStore::whereRaw(1)->orderBy('rs_name');
    $res = BaseRepository::getToArrayGet($res);

    return $res;
}

/* -卖场促销可使用店铺范围 */
function get_favourable_merchants($userFav_type = 0, $userFav_type_ext = '', $rs_id = 0, $type = 0, $ru_id = 0)
{
    if ($userFav_type != GENERAL_AUDIENCE && !empty($userFav_type_ext)) {
        if ($rs_id > 0) {
            if ($type == 1) {//返回数组 否则返回字符串
                if ($ru_id) {//如果传入商家ID，判断是否在活动范围内 返回 TURE OR FALSE;
                    if (in_array($ru_id, explode(",", $userFav_type_ext))) {
                        return true;
                    } else {
                        return false;
                    }
                } else {//否则返回店铺数组
                    return explode(",", $userFav_type_ext);
                }
            } else {
                return $userFav_type_ext;
            }
        } else {
            $res = RsRegion::whereIn('rs_id', $userFav_type_ext);

            $res = $res->whereHasIn('getMerchantsShopInformation');

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                $res = collect($res)->flatten();
                $res = $res->all();

                if ($type == 1) {//返回数组 否则返回字符串
                    if ($ru_id) {//如果传入商家ID，判断是否在活动范围内 返回 TURE OR FALSE;
                        if (in_array($ru_id, $res)) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return $res;
                    }
                } else {
                    return implode(",", $res);
                }
            }
        }
    } elseif ($userFav_type != GENERAL_AUDIENCE && empty($userFav_type_ext)) {
        return;
    }
}

/* *
 * 缓存分类
 * 查询所有分类
 */
function get_fine_all_category($is_show = 0)
{
    $arr = read_static_cache('get_fine_all_category_' . $is_show);

    if ($arr === false) {
        $arr = Category::where('is_show', $is_show);
        $arr = BaseRepository::getToArrayGet($arr);

        write_static_cache('get_fine_all_category_' . $is_show, $arr);
    }

    return $arr;
}

/**
 * 修改智能权重里的对商家评价的数量
 * @param int $goods_id 订单商品id
 * @param array $num 商家评价的数量
 * @return  bool
 */
function update_comment_seller($goods_id, $num)
{
    $res = IntelligentWeight::select('merchants_comment_number', 'goods_id')->where('goods_id', $goods_id);
    $res = BaseRepository::getToArrayFirst($res);

    if ($res) {
        $num['merchants_comment_number'] = $num['merchants_comment_number'] + $res['merchants_comment_number'];

        IntelligentWeight::where('goods_id', $goods_id)->update($num);
    } else {
        $num['merchants_comment_number'] = 0;

        IntelligentWeight::insert($num);
    }

    update_goods_weights($goods_id); // 更新权重值
}

/**
 * 修改智能权重里的对关注商品的数量
 * @param int $goods_id 订单商品id
 * @param array $num 商家评价的数量
 * @return  bool
 */
function update_attention_num($goods_id, $num)
{
    $res = IntelligentWeight::select('user_attention_number', 'goods_id')->where('goods_id', $goods_id);
    $res = BaseRepository::getToArrayFirst($res);

    if ($res) {
        $num['user_attention_number'] = $num['user_attention_number'] + $res['user_attention_number'];

        IntelligentWeight::where('goods_id', $goods_id)->update($num);
    } else {
        IntelligentWeight::insert($num);
    }

    update_goods_weights($goods_id); // 更新权重值
}

//改变订单编号
function correct_order_sn($order_sn = '')
{
    $new_order_sn = get_order_sn();//生成新的订单号

    //修改订单表订单号
    OrderInfo::where('order_sn', $order_sn)
        ->update(['order_sn' => $new_order_sn]);

    //修改分期表订单号
    Stages::where('order_sn', $order_sn)
        ->update(['order_sn' => $new_order_sn]);

    //修改交易快照订单号
    TradeSnapshot::where('order_sn', $order_sn)
        ->update(['order_sn' => $new_order_sn]);

    //修改虚拟卡卡号库表订单号
    VirtualCard::where('order_sn', $order_sn)
        ->update(['order_sn' => $new_order_sn]);

    //修改交易纠纷表订单号
    Complaint::where('order_sn', $order_sn)
        ->update(['order_sn' => $new_order_sn]);

    //修改账户日志订单号
    $new_order_sn_msg = "，订单号已修改为：$new_order_sn";
    $other = [
        'change_desc' => DB::raw("concat(change_desc,'$new_order_sn_msg')")
    ];
    AccountLog::where('change_desc', 'like', '%' . $order_sn . '%')->update($other);

    //修改管理员日志订单号
    $new_order_sn_msg = "，订单号已修改为：$new_order_sn";
    $other = [
        'log_info' => DB::raw("concat(log_info,'$new_order_sn_msg')")
    ];
    AdminLog::where('log_info', 'like', '%' . $order_sn . '%')->update($other);

    return $new_order_sn;
}

/**
 * 获取个页面的seo
 */
function get_seo_words($type)
{
    $res = Seo::where('type', $type);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 获取分类页的seo
 */
function get_category_seo_words($cat_id)
{
    $res = Category::select('cate_title', 'cate_keywords', 'cate_description')->where('cat_id', $cat_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

/**
 * 推送微信通模板消息 curl 基于微信通
 * @param string $code
 * @param array $pushData
 * @param string $order_url
 * @param int $user_id
 * @return
 */
function push_template_curl($code = '', $pushData = [], $order_url = '', $user_id = 0, $shop_url = '')
{
    if (!empty($pushData)) {
        $order_url = urlencode(base64_encode($order_url));

        //以json格式传输
        $data = urlencode(serialize($pushData));
        $api_url = $shop_url . 'wechat/api?user_id=' . $user_id . '&code=' . urlencode($code) . '&pushData=' . $data . '&url=' . $order_url;
        return curlGet_weixin($api_url); // curl请求接口
    }
}

/**
 * 友好的时间显示
 *
 * @param int $sTime 待显示的时间
 * @param string $type 类型. normal | mohu | full | ymd | other
 * @param string $alt 已失效
 * @return string
 */
function friendlyDate($sTime, $type = 'normal', $alt = 'false')
{
    if (!$sTime) {
        return '';
    }
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("z", $cTime)) - intval(date("z", $sTime));
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if ($type == 'normal') {
        if ($dTime < 60) {
            if ($dTime < 10) {
                return lang('common.friendly.opportunity.0');    //by yangjs
            } else {
                return intval(floor($dTime / 10) * 10) . lang('common.friendly.opportunity.7');
            }
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . lang('common.friendly.opportunity.1');
            //今天的数据.年份相同.日期相同.
        } elseif ($dYear == 0 && $dDay == 0) {
            return lang('common.friendly.opportunity.8') . date('H:i', $sTime);
        } elseif ($dYear == 0) {
            return date("m月d日 H:i", $sTime);
        } else {
            return date("Y-m-d H:i", $sTime);
        }
    } elseif ($type == 'mohu') {
        if ($dTime < 60) {
            return $dTime . lang('common.friendly.opportunity.7');
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . lang('common.friendly.opportunity.1');
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . lang('common.friendly.opportunity.2');
        } elseif ($dDay > 0 && $dDay <= 7) {
            return intval($dDay) . lang('common.friendly.opportunity.3');
        } elseif ($dDay > 7 && $dDay <= 30) {
            return intval($dDay / 7) . lang('common.friendly.opportunity.9');
        } elseif ($dDay > 30) {
            return intval($dDay / 30) . lang('common.friendly.opportunity.10');
        }
        //full: Y-m-d , H:i:s
    } elseif ($type == 'full') {
        return date("Y-m-d , H:i:s", $sTime);
    } elseif ($type == 'ymd') {
        return date("Y-m-d", $sTime);
    } elseif ($type == 'moremohu') {
        if ($dTime < 60) {
            return $dTime . lang('common.friendly.opportunity.7');
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . lang('common.friendly.opportunity.1');
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . lang('common.friendly.opportunity.2');
        } elseif ($dDay > 0 && $dDay <= 7) {
            return intval($dDay) . lang('common.friendly.opportunity.3');
        } elseif ($dDay > 7 && $dDay <= 30) {
            return intval($dDay / 7) . lang('common.friendly.opportunity.9');
        } else {
            return lang('common.friendly.opportunity.10');
        }
    } else {
        if ($dTime < 60) {
            return $dTime . lang('common.friendly.opportunity.7');
        } elseif ($dTime < 3600) {
            return intval($dTime / 60) . lang('common.friendly.opportunity.1');
        } elseif ($dTime >= 3600 && $dDay == 0) {
            return intval($dTime / 3600) . lang('common.friendly.opportunity.2');
        } elseif ($dYear == 0) {
            return date("Y-m-d H:i:s", $sTime);
        } else {
            return date("Y-m-d H:i:s", $sTime);
        }
    }
}

/**
 * 修改智能权重里的订单商品数量
 * @param int $goods_id 订单商品id
 * @param array $num 订单商品数量
 * @return  bool
 */
function update_manual($goods_id, $num)
{
    $goods_number = IntelligentWeight::where('goods_id', $goods_id)->value('goods_number');

    $num['goods_number'] += $goods_number;
    if ($goods_number) {
        $res = IntelligentWeight::where('goods_id', $goods_id)->update($num);
    } else {
        $res = IntelligentWeight::insertGetId($num);
    }

    update_goods_weights($goods_id); // 更新权重值

    if ($res) {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断商家等级是否到期
 * @return bool
 */
function judge_seller_grade_expiry($ru_id = 0)
{
    if ($ru_id > 0) {
        $seller_grade = get_seller_grade($ru_id);
        $end_time = $seller_grade ? TimeRepository::getLocalDate('Y', $seller_grade['add_time']) + $seller_grade['year_num'] . '-' . TimeRepository::getLocalDate('m-d H:i:s', $seller_grade['add_time']) : '';
        $end_stamp = TimeRepository::getLocalStrtoTime($end_time);
        $is_expiry = (TimeRepository::getGmTime() > $end_stamp) ? true : false;

        return $is_expiry;
    }

    return false;
}

/**
 * curl 获取
 */
function curlGet_weixin($url, $timeout = 5, $header = "")
{
    $defaultHeader = '$header = "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.%d Safari/537.%d\r\n";
        $header .= "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $header .= "Accept-language: zh-cn,zh;q=0.5\r\n";
        $header .= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
        $header = sprintf($header, time(), time() + rand(1000, 9999));';

    $header = empty($header) ? $defaultHeader : $header;
    $ch = curl_init();
    if (stripos($url, "https://") !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$header]); //模拟的header头
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

//将时间转化为刚刚、几分钟前等等 by wu
function get_time_past($time = 0, $now = 0)
{
    $time_past = "";

    if ($now >= $time) {
        //相差时间
        $diff = $now - $time;

        //一分钟内：刚刚
        if ($diff > 0 && $diff <= 60) {
            $time_past = $GLOBALS['_LANG']['Opportunity'][0];
        } //一小时内：n分钟前
        elseif ($diff > 60 && $diff <= 3600) {
            $time_past = floor($diff / 60) . $GLOBALS['_LANG']['Opportunity'][1];
        } //一天内：n小时前
        elseif ($diff > 3600 && $diff <= 86400) {
            $time_past = floor($diff / 3600) . $GLOBALS['_LANG']['Opportunity'][2];
        } //一月内：n天前
        elseif ($diff > 86400 && $diff <= 2592000) {
            $time_past = floor($diff / 86400) . $GLOBALS['_LANG']['Opportunity'][3];
        } //一年内：n月前
        elseif ($diff > 2592000 && $diff <= 31536000) {
            $time_past = floor($diff / 2592000) . $GLOBALS['_LANG']['Opportunity'][4];
        } //一年后：n年前
        elseif ($diff > 31536000) {
            $time_past = floor($diff / 31536000) . $GLOBALS['_LANG']['Opportunity'][5];
        }
    } else {
        $time_past = $GLOBALS['_LANG']['Opportunity'][6];
    }

    return $time_past;
}

/**
 * 获得快递名称 by leah
 * @param type $shipping_id
 * @return type
 */
function get_shipping_name($shipping_id)
{
    return Shipping::where('shipping_id', $shipping_id)->value('shipping_name');
}

/*************************************************************** 处理字符串 start *************************************************************/

/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param $str 被截取的字符串
 * @param int $length 截取的长度
 * @param bool $append 是否附加省略号
 * @return bool|string
 */
function sub_str($str, $length = 0, $append = true)
{
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength) {
        return $str;
    } elseif ($length < 0) {
        $length = $strlength + $length;
        if ($length < 0) {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr')) {
        $newstr = mb_substr($str, 0, $length, EC_CHARSET);
    } elseif (function_exists('iconv_substr')) {
        $newstr = iconv_substr($str, 0, $length, EC_CHARSET);
    } else {
        $newstr = substr($str, 0, $length);
    }

    if ($append && $str != $newstr) {
        $newstr .= '...';
    }

    return $newstr;
}

/**
 * 获取商品权重值
 * @param int $goods_id
 * @return int
 */
function get_goods_weights($goods_id = 0)
{
    $row = IntelligentWeight::selectRaw("(goods_number - return_number + user_number + goods_comment_number + merchants_comment_number + user_attention_number) AS weights")->where('goods_id', $goods_id);

    $row = BaseRepository::getToArrayFirst($row);

    return $row ? $row['weights'] : 0;
}

/**
 * 更新商品权重值
 * 商品权重值最终计算公式：商品权重值 = 商品购买数量 - 商品退换货数量 + 购买此商品的会员数量 + 对商品评价数量 + 对商品的商家评价数量 + 会员关注此商品数量 + 人工干预值
 * @param int $goods_id
 * @return mixed
 */
function update_goods_weights($goods_id = 0)
{
    $weights = get_goods_weights($goods_id);
    $sort_order = Goods::where('goods_id', $goods_id)->value('sort_order');
    $last_weights = $sort_order + $weights;

    return Goods::where('goods_id', $goods_id)->update(['weights' => $last_weights]);
}
