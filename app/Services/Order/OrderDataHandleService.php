<?php

namespace App\Services\Order;

use App\Models\BaitiaoLog;
use App\Models\DeliveryOrder;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\OrderReturn;
use App\Models\OrderSettlementLog;
use App\Models\Region;
use App\Models\ReturnGoods;
use App\Models\SellerBillOrder;
use App\Models\SellerNegativeOrder;
use App\Models\StoreOrder;
use App\Models\TeamGoods;
use App\Models\TeamLog;
use App\Models\TradeSnapshot;
use App\Models\ValueCardRecord;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\TimeRepository;

class OrderDataHandleService
{
    /**
     * 订单列表
     *
     * @param array $order_id
     * @param array $data
     * @param int $type
     * @return array
     */
    public static function orderDataList($order_id = [], $data = [], $type = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $data = empty($data) ? "*" : $data;

        $res = OrderInfo::select($data);

        if ($type == 1) {
            $res = $res->whereIn('main_order_id', $order_id)
                ->where('main_count', 0);
        } else {
            $res = $res->whereIn('order_id', $order_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if ($type == 1) {
                    $arr[$val['main_order_id']][] = $val;
                } else {
                    $arr[$val['order_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 是否分销订单
     *
     * @param array $order_id
     * @return array
     */
    public static function isDrpOrder($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $res = OrderGoods::distinct()->select('order_id')
            ->whereIn('order_id', $order_id)
            ->where('is_distribution', 1)
            ->where('drp_money', '>', 0);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']]['is_drp'] = 1;
            }
        }

        return $arr;
    }

    /**
     * 是否门店订单
     *
     * @param array $order_id
     * @return array
     */
    public static function isStoreOrder($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $res = StoreOrder::distinct()->select('order_id')
            ->whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']]['is_store'] = 1;
            }
        }

        return $arr;
    }

    /**
     * 订单是否分期
     *
     * @param array $order_id
     * @return array
     */
    public static function isStagesBaiTiao($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $res = BaitiaoLog::distinct()->select('order_id', 'is_stages')
            ->whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 订单储值卡信息
     *
     * @param array $order_id
     * @return array
     */
    public static function orderValueCardRecord($order_id = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $res = ValueCardRecord::distinct()->select('order_id', 'use_val')->whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取订单地区
     *
     * @param array $country
     * @param array $province
     * @param array $city
     * @param array $district
     * @param array $street
     * @return array
     */
    public static function orderRegionAddress($country = [], $province = [], $city = [], $district = [], $street = [])
    {

        $orderRep = new self();

        $arr = [];
        if ($country) {
            $countryRegion = array_unique($country);
            $countryRegion = array_values($countryRegion);

            $country_list = Region::select('region_id', 'region_name')
                ->whereIn('region_id', $countryRegion)
                ->where('region_type', 0);
            $country_list = BaseRepository::getToArrayGet($country_list);

            $arr['country'] = $orderRep->regionList($country_list, $country);
        }

        if ($province) {
            $provinceRegion = array_unique($province);
            $provinceRegion = array_values($provinceRegion);

            $province_list = Region::select('region_id', 'region_name')
                ->whereIn('region_id', $provinceRegion)
                ->where('region_type', 1);
            $province_list = BaseRepository::getToArrayGet($province_list);

            $arr['province'] = $orderRep->regionList($province_list, $province);
        }

        if ($city) {
            $city_list = Region::select('region_id', 'region_name')
                ->whereIn('region_id', $city)
                ->where('region_type', 2);
            $city_list = BaseRepository::getToArrayGet($city_list);

            $arr['city'] = $orderRep->regionList($city_list, $city);
        }

        if ($district) {
            $district_list = Region::select('region_id', 'region_name')
                ->whereIn('region_id', $district)
                ->where('region_type', 3);
            $district_list = BaseRepository::getToArrayGet($district_list);

            $arr['district'] = $orderRep->regionList($district_list, $district);
        }

        if ($street) {
            $street_list = Region::select('region_id', 'region_name')
                ->whereIn('region_id', $street)
                ->where('region_type', 4);
            $street_list = BaseRepository::getToArrayGet($street_list);

            $arr['street'] = $orderRep->regionList($street_list, $street);
        }

        return $arr;
    }

    /**
     * 返回地区值
     *
     * @param array $list
     * @param array $region
     * @return array
     */
    private function regionList($list = [], $region = [])
    {
        $arr = [];
        if ($list) {
            foreach ($region as $key => $val) {
                $sql = [
                    'where' => [
                        [
                            'name' => 'region_id',
                            'value' => $val
                        ]
                    ]
                ];
                $row = BaseRepository::getArraySqlFirst($list, $sql);

                $arr[$key] = $row;
            }
        }
        return $arr;
    }

    /**
     * 组合地区
     *
     * @param string $country
     * @param string $province
     * @param string $city
     * @param string $district
     * @param string $street
     * @param string $str
     * @return string
     */
    public static function orderRegionCombination($country = '', $province = '', $city = '', $district = '', $street = '', $str = ' ')
    {
        $region = '';

        if ($country) {
            $region .= $country . $str;
        }

        if ($province) {
            $region .= $province . $str;
        }

        if ($city) {
            $region .= $city . $str;
        }

        if ($district) {
            $region .= $district . $str;
        }

        if ($street) {
            $region .= $street . $str;
        }

        return rtrim($region, ' ');
    }

    /**
     * 普通退换货订单商品列表
     *
     * @param array $id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getOrderReturnDataList($id = [], $data = [], $field = 'rec_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = OrderReturn::select($data);

        if (stripos($field, 'ret_id') !== false) {
            $res = $res->whereIn('ret_id', $id);
        } elseif (stripos($field, 'order_id') !== false) {
            $res = $res->whereIn('order_id', $id);
        } else {
            $res = $res->whereIn('rec_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (stripos($field, 'ret_id') !== false) {
                    $arr[$row['ret_id']] = $row;
                } elseif (stripos($field, 'order_id') !== false) {
                    $arr[$row['order_id']][] = $row;
                } else {
                    $arr[$row['rec_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 普通退换货订单商品列表
     *
     * @param array $id
     * @param array $data
     * @param string $field
     * @param int $limit
     * @return array
     */
    public static function getReturnGoodsDataList($id = [], $data = [], $field = 'rec_id', $limit = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = $id ? array_unique($id) : [];

        $data = $data ? $data : '*';

        $res = ReturnGoods::select($data);

        if (stripos($field, 'ret_id') !== false) {
            $res = $res->whereIn('ret_id', $id);
        } else {
            $res = $res->whereIn('rec_id', $id);
        }

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                if (stripos($field, 'ret_id') !== false) {
                    $arr[$row['ret_id']][] = $row;
                } else {
                    $arr[$row['rec_id']] = $row;
                }
            }
        }

        return $arr;
    }

    /**
     * 订单商品列表
     *
     * @param array $id
     * @param array $data
     * @param int $type
     * @return array
     */
    public static function orderGoodsDataList($id = [], $data = [], $type = 0)
    {
        $id = BaseRepository::getExplode($id);

        if (empty($id)) {
            return [];
        }

        $id = array_unique($id);

        $data = empty($data) ? "*" : $data;

        $res = OrderGoods::select($data);

        if ($type == 1) {
            $res = $res->whereIn('order_id', $id);
        } else {
            $res = $res->whereIn('rec_id', $id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                if ($type == 1) {
                    $arr[$val['order_id']][] = $val;
                } else {
                    $arr[$val['rec_id']] = $val;
                }
            }
        }

        return $arr;
    }

    /**
     * 获取会员账单订单记录信息列表
     *
     * @param array $order_id
     * @param array $data
     * @return array
     */
    public static function getBillOrderDataList($order_id = [], $data = [])
    {
        $order_id = BaseRepository::getExplode($order_id);

        if (empty($order_id)) {
            return [];
        }

        $order_id = $order_id ? array_unique($order_id) : [];

        $data = $data ? $data : '*';

        $res = SellerBillOrder::select($data)->whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['order_id']] = $row;
            }
        }

        return $arr;
    }

    /**
     * 获取会员账单订单记录信息列表
     *
     * @param $seller_id
     * @param array $data
     * @return array
     */
    public static function getSellerBillOrderDataList($seller_id, $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = SellerBillOrder::select($data)->whereIn('seller_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['seller_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 获取会员账单确认收货订单记录信息列表
     *
     * @param $seller_id
     * @param array $data
     * @return array
     */
    public static function getSellerOrderSettlementLogDataList($seller_id, $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = OrderSettlementLog::select($data)->whereIn('seller_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['seller_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 获取会员账单确认收货订单记录信息列表
     *
     * @param array $seller_id
     * @param array $data
     * @return array
     */
    public static function getSellerNegativeOrderDataList($seller_id = [], $data = [])
    {
        $seller_id = BaseRepository::getExplode($seller_id);

        if (empty($seller_id)) {
            return [];
        }

        $seller_id = $seller_id ? array_unique($seller_id) : [];

        $data = $data ? $data : '*';

        $res = SellerNegativeOrder::select($data)->whereIn('seller_id', $seller_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['seller_id']][] = $row;
            }
        }

        return $arr;
    }

    /**
     * 订单退换货列表
     *
     * @param array $order_id
     * @param array $data
     * @param int $type
     * @return array
     */
    public static function orderIdReturnDataList($order_id = [], $data = [], $type = 0)
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $data = empty($data) ? "*" : $data;

        $res = orderReturn::select($data);

        if ($type == 1) {
            $res = $res->whereIn('main_order_id', $order_id)
                ->where('main_count', 0);
        } else {
            $res = $res->whereIn('order_id', $order_id);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 广告订单统计
     *
     * @param array $ad_id
     * @param array $data
     * @param int $limit
     * @return array
     */
    public static function fromAdOrderList($ad_id = [], $data = [], $limit = 0)
    {
        $ad_id = BaseRepository::getExplode($ad_id);

        if (empty($ad_id)) {
            return [];
        }

        $ad_id = array_unique($ad_id);

        $data = empty($data) ? "*" : $data;

        $ad_id = array_unique($ad_id);

        $res = OrderInfo::select($data)->whereIn('from_ad', $ad_id);

        if ($limit > 0) {
            $res = $res->take($limit);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr['from_ad'][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 发货单号
     *
     * @param array $order_id
     * @param array $data
     * @return array
     */
    public static function getDeliveryOrderDataList($order_id = [], $data = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $data = empty($data) ? "*" : $data;

        $res = DeliveryOrder::select($data)
            ->whereIn('order_id', $order_id);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['order_id']][] = $val;
            }
        }

        return $arr;
    }

    /**
     * 获取拼团信息,验证失败提示
     *
     * @param array $teamIdList
     * @return array
     */
    public static function getOrderTeamList($teamIdList = [])
    {
        $teamIdList = ArrRepository::getArrayUnset($teamIdList);

        if (empty($teamIdList)) {
            return [];
        }

        $order_id = BaseRepository::getArrayKeys($teamIdList);

        $time = TimeRepository::getGmTime();

        $orderSer = new self();
        $orderList = $orderSer->orderDataList($order_id, ['order_id', 'team_id', 'order_status', 'pay_status']);

        if (empty($orderList)) {
            return [];
        }

        $team_id = BaseRepository::getKeyPluck($orderList, 'team_id');

        $res = TeamLog::select('team_id', 't_id', 'start_time', 'status')
            ->whereIn('team_id', $team_id);
        $res = BaseRepository::getToArrayGet($res);

        $t_id = BaseRepository::getKeyPluck($res, 't_id');

        $teamGoodsList = TeamGoods::query()->select('id', 'team_num', 'validity_time', 'is_team')
            ->whereIn('id', $t_id);
        $teamGoodsList = BaseRepository::getToArrayGet($teamGoodsList);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {

                $sql = [
                    'where' => [
                        [
                            'name' => 'team_id',
                            'value' => $row['team_id']
                        ]
                    ]
                ];
                $order = BaseRepository::getArraySqlFirst($orderList, $sql);

                $team_info = [];
                $team_info['order_id'] = $order['order_id'] ?? 0;
                $team_info['order_status'] = $order['order_status'] ?? 0;
                $team_info['pay_status'] = $order['pay_status'] ?? 0;

                $sql = [
                    'where' => [
                        [
                            'name' => 'id',
                            'value' => $row['t_id']
                        ]
                    ]
                ];
                $teamGoods = BaseRepository::getArraySqlFirst($teamGoodsList, $sql);

                $team_info['team_num'] = $teamGoods['team_num'] ?? 0;
                $team_info['validity_time'] = $teamGoods['validity_time'] ?? 0;
                $team_info['is_team'] = $teamGoods['is_team'] ?? 0;

                $team_info['start_time'] = $row['start_time'] ?? 0;
                $team_info['status'] = $row['is_team'] ?? 0;

                $end_time = ($team_info['start_time'] + ($team_info['validity_time'] * 3600));

                if ($time < $end_time && $team_info['status'] == 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 4) {
                    //参团 ：拼团完成、未结束、未付款订单过期
                    $arr[$row['team_id']]['failure'] = 1;
                } elseif ($time > $end_time && $team_info['status'] == 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 4) {
                    //参团 ：拼团结束，完成，未付款过期
                    $arr[$row['team_id']]['failure'] = 1;
                } elseif (($time > $end_time || $time < $end_time) && $team_info['status'] != 1 && $team_info['order_status'] == 2) {
                    //订单取消
                    $arr[$row['team_id']]['failure'] = 1;
                } elseif ($time > $end_time && $team_info['status'] != 1 && $team_info['pay_status'] != 2 && $team_info['order_status'] != 2) {
                    //未付款
                    $arr[$row['team_id']]['failure'] = 1;
                } elseif ($team_info['status'] != 1 && ($time > $end_time || $team_info['is_team'] != 1)) {
                    //开团：未成功
                    $arr[$row['team_id']]['failure'] = 1;
                } else {
                    $arr[$row['team_id']]['failure'] = 0;
                }
            }
        }

        return $arr;
    }

    /**
     * 订单快照
     *
     * @param array $order_id
     * @param array $data
     * @return array
     */
    public static function getTradeSnapshotDataList($order_id = [], $data = [])
    {
        if (empty($order_id)) {
            return [];
        }

        $order_id = array_unique($order_id);

        $data = empty($data) ? "*" : $data;

        $res = TradeSnapshot::select($data)->whereIn('order_id', $order_id);
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $val) {
                $arr[$val['trade_id']] = $val;
            }
        }

        return $arr;
    }
}
