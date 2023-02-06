<?php

namespace App\Services\Seckill;

use App\Models\Goods;
use App\Models\Seckill;
use App\Models\SeckillTimeBucket;
use App\Repositories\Activity\SeckillRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * Class SeckillManageService
 * @package App\Services\Seckill
 */
class SeckillManageService
{
    protected $merchantCommonService;
    protected $categoryService;

    public function __construct(
        MerchantCommonService $merchantCommonService,
        CategoryService $categoryService
    )
    {
        $this->merchantCommonService = $merchantCommonService;
        $this->categoryService = $categoryService;
    }

    /*
    *  秒杀活动列表
    */
    public function getSeckillList()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'getSeckillList';
        $get_filter = app(DscRepository::class)->getSessionFilter($param_str);

        $_REQUEST = !empty($get_filter) ? BaseRepository::getArrayMerge($_REQUEST, $get_filter) : $_REQUEST;

        $adminru = get_admin_ru_id();

        /* 查询条件 */
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'sec_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['seller_list'] = isset($_REQUEST['seller_list']) && !empty($_REQUEST['seller_list']) ? 1 : 0;  //商家和自营订单标识
        $filter['review_status'] = empty($_REQUEST['review_status']) ? 0 : intval($_REQUEST['review_status']);
        $filter['sec_id'] = isset($_REQUEST['sec_id']) ? intval($_REQUEST['sec_id']) : 0;

        $res = Seckill::whereRaw(1);
        if (!empty($filter['sec_id'])) {
            $res = $res->where('sec_id', $filter['sec_id']);
        }
        if (!empty($filter['keywords'])) {
            $res = $res->where('acti_title', 'LIKE', '%' . mysql_like_quote($filter['keywords']) . '%');
        }
        //区分商家和自营
        if (!empty($filter['seller_list'])) {
            $res = $res->where('ru_id', '>', 0);
        } else {
            $res = $res->where('ru_id', 0);
        }

        if ($filter['review_status']) {
            $res = $res->where('review_status', $filter['review_status']);
        }

        //管理员查询的权限 -- 店铺查询 start
        $filter['store_search'] = !isset($_REQUEST['store_search']) ? -1 : intval($_REQUEST['store_search']);
        $filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
        $filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';

        if ($filter['store_search'] > -1) {
            if ($adminru['ru_id'] == 0) {
                if ($filter['store_search'] > 0) {
                    $store_type = isset($_REQUEST['store_type']) && !empty($_REQUEST['store_type']) ? intval($_REQUEST['store_type']) : 0;

                    if ($filter['store_search'] == 1) {
                        $res = $res->where('ru_id', $filter['merchant_id']);
                    }

                    if ($filter['store_search'] > 1) {
                        $res = $res->where(function ($query) use ($filter, $store_type) {
                            $query = $query->whereHasIn('getMerchantsShopInformation', function ($query) use ($filter, $store_type) {
                                if ($filter['store_search'] == 2) {
                                    $query = $query->where('rz_shop_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                } elseif ($filter['store_search'] == 3) {
                                    $query = $query->where('shoprz_brand_name', 'LIKE', '%' . mysql_like_quote($filter['store_keyword']) . '%');
                                    if ($store_type) {
                                        $query = $query->where('shop_name_suffix', $store_type);
                                    }
                                }
                            });
                        });
                    }
                } else {
                    $res = $res->where('ru_id', 0);
                }
            }
        }
        //管理员查询的权限 -- 店铺查询 end

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        // 存储最后一次过滤条件
        app(DscRepository::class)->setSessionFilter($filter, $param_str);

        /* 获活动数据 */
        $res = $res->orderBy($filter['sort_by'], $filter['sort_order'])
            ->offset($filter['start'])
            ->limit($filter['page_size']);
        $row = BaseRepository::getToArrayGet($res);

        $time = TimeRepository::getGmTime();

        if ($row) {

            $ru_id = BaseRepository::getKeyPluck($row, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $key => $val) {
                $row[$key]['begin_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $val['begin_time']);
                $row[$key]['acti_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $val['acti_time']);
                $start_time = TimeRepository::getLocalStrtoTime($row[$key]['begin_time']);
                $end_time = TimeRepository::getLocalStrtoTime($row[$key]['acti_time']);
                if ($time > $end_time) {
                    $row[$key]['time'] = $GLOBALS['_LANG']['activity_end'];
                } elseif ($time < $end_time && $time > $start_time) {
                    $row[$key]['time'] = $GLOBALS['_LANG']['activity_have_hand'];
                } else {
                    $row[$key]['time'] = $GLOBALS['_LANG']['activity_not_started'];
                }
                $row[$key]['ru_name'] = $merchantList[$val['ru_id']]['shop_name'] ?? '';
            }
        }

        $arr = ['seckill' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];

        return $arr;
    }

    /*
    * 秒杀活动详情
    */
    public function getSeckillInfo()
    {
        $res = Seckill::where('sec_id', intval($_REQUEST['sec_id']));
        $arr = BaseRepository::getToArrayFirst($res);

        if (!empty($arr)) {
            $arr['begin_time'] = TimeRepository::getLocalDate("Y-m-d", $arr['begin_time']);
            $arr['acti_time'] = TimeRepository::getLocalDate("Y-m-d", $arr['acti_time']);
        }
        return $arr;
    }

    public function getTimeBucketList()
    {
        $res = SeckillTimeBucket::whereRaw(1);
        $res = BaseRepository::getToArrayGet($res);
        return $res;
    }

    public function getTimeBucketInfo($id)
    {
        $res = SeckillTimeBucket::where('id', $id);
        $row = BaseRepository::getToArrayFirst($res);
        if ($row) {
            $begin_time = explode(':', $row['begin_time']);
            $row['begin_hour'] = $begin_time[0];
            $row['begin_minute'] = $begin_time[1];
            $row['begin_second'] = $begin_time[2];
            $end_time = explode(':', $row['end_time']);
            $row['end_hour'] = $end_time[0];
            $row['end_minute'] = $end_time[1];
            $row['end_second'] = $end_time[2];
        }
        return $row;
    }

    //比对开始结束时间大小
    public function contrastTime($begin_time, $end_time)
    {
        $local_begin_time = TimeRepository::getLocalStrtoTime($begin_time);
        $local_end_time = TimeRepository::getLocalStrtoTime($end_time);

        if ($local_begin_time >= $local_end_time) {
            return false;
        }
        return true;
    }

    //当编辑结束时间时判断是否在可修改范围内
    public function editEndTime($tb_id, $end_time)
    {
        $res = SeckillTimeBucket::where('id', $tb_id);
        $row = BaseRepository::getToArrayFirst($res);

        $old_end_time = $row['end_time'] ?? '';
        $formated_old_end_time = explode(':', $old_end_time);
        $formated_old_end_time[2] = str_pad($formated_old_end_time[2] + 1, 2, "0", STR_PAD_LEFT);
        $old_end_time = implode(':', $formated_old_end_time);

        $formated_next_begin_time = explode(':', $end_time);
        $formated_next_begin_time[2] = str_pad($formated_next_begin_time[2] + 1, 2, "0", STR_PAD_LEFT);
        $edit_begin_time = implode(':', $formated_next_begin_time);

        $next_end_time = SeckillTimeBucket::where('begin_time', $old_end_time)->value('end_time');
        $next_end_time = $next_end_time ? $next_end_time : 0;

        if ($next_end_time) {
            if ($this->contrastTime($end_time, $next_end_time)) {
                $data = ['begin_time' => $edit_begin_time];
                SeckillTimeBucket::where('begin_time', $old_end_time)->update($data);

                return true;
            };
        } else {
            return true;
        }

        return false;
    }

    /**
     * 设置商品模块列表
     * @param array $where
     * @return array
     */
    public function getPisGoodsList($where = [])
    {
        $where['goods_ids'] = isset($where['goods_ids']) ? BaseRepository::getExplode($where['goods_ids']) : $where['goods_ids'];

        $row = Goods::where('is_delete', 0)
            ->where('is_on_sale', 1)
            ->where('is_real', 1);

        if (config('shop.review_goods') == 1) {
            $row = $row->whereIn('review_status', [3, 4, 5]);
        }

        if (isset($where['cat_id']) && isset($where['cat_id'][0]) && $where['cat_id'][0] > 0) {
            $children = $this->categoryService->getCatListChildren($where['cat_id'][0]);

            if ($children) {
                $row = $row->whereIn('cat_id', $children);
            }
        }

        if (isset($where['brand_id']) && $where['brand_id'] > 0) {
            $row = $row->where('brand_id', $where['brand_id']);
        }

        // 商家商品或平台商品
        if (isset($where['user_id']) && $where['user_id'] > 0) {
            $row = $row->where('user_id', $where['user_id']);
        } else {
            $row = $row->where('user_id', 0);
        }

        if (isset($where['keyword']) && $where['keyword']) {
            $row = $row->where('goods_name', 'like', '%' . $where['keyword'] . '%')
                ->orWhere('goods_sn', 'like', '%' . $where['keyword'] . '%');
        }

        if (isset($where['goods_ids']) && isset($where['type']) && $where['goods_ids'] && $where['type'] == '0') {
            $row = $row->whereIn('goods_id', $where['goods_ids']);
        }

        $res = $record_count = $row;

        if (isset($where['is_page']) && $where['is_page'] == 1) {
            $filter['record_count'] = $record_count->count();

            $filter = page_and_size($filter);
        }

        switch (isset($where['sort_order']) && $where['sort_order']) {
            case 1:
                $res = $res->orderBy("add_time");
                break;

            case 2:
                $res = $res->orderBy("add_time", 'desc');
                break;

            case 3:
                $res = $res->orderBy("sort_order");
                break;

            case 4:
                $res = $res->orderBy("sort_order", 'desc');
                break;

            case 5:
                $res = $res->orderBy("goods_name");
                break;

            case 6:
                $res = $res->orderBy("goods_name", 'desc');
                break;
            default:
                $res = $res->orderByRaw("sort_order, sales_volume desc");
                break;
        }

        if (isset($where['is_page']) && $where['is_page'] == 1) {
            if ($filter['start'] > 0) {
                $res = $res->skip($filter['start']);
            }

            if ($filter['page_size'] > 0) {
                $res = $res->take($filter['page_size']);
            }
        }

        $res = BaseRepository::getToArrayGet($res);

        if (isset($where['is_page']) && $where['is_page'] == 1) {
            $filter['page_arr'] = seller_page($filter, $filter['page']);
            return ['list' => $res, 'filter' => $filter];
        } else {
            return $res;
        }
    }

    /**
     * 获得秒杀活动的商品
     *
     * @access  public
     * @param int $sec_id
     * @param int $tb_id
     * @return  array
     */
    public function get_add_seckill_goods($sec_id = 0, $tb_id = 0)
    {
        $row = SeckillRepository::get_add_seckill_goods($sec_id, $tb_id);

        $seckill_goods = $row['seckill_goods'] ?? [];
        foreach ($seckill_goods as $key => $val) {
            // 秒杀商品是否有设置属性
            $seckill_goods_id = $val['id'] ?? 0;
            $goods_id = $val['goods_id'] ?? 0;
            $seckill_goods[$key]['seckill_product_list'] = SeckillGoodsAttrService::getSeckillGoodsAttr($seckill_goods_id, $goods_id);
        }

        $row['seckill_goods'] = $seckill_goods;
        return $row;
    }
}
