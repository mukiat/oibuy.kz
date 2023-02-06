<?php

namespace App\Services\Activity;

use App\Models\MerchantsShopInformation;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscEncryptRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Category\CategoryService;
use App\Services\ValueCard\ValueCardDataHandleService;

class ValueCardService
{
    protected $dscRepository;
    protected $valueCardDataHandleService;
    protected $categoryService;

    public function __construct(
        DscRepository $dscRepository,
        ValueCardDataHandleService $valueCardDataHandleService,
        CategoryService $categoryService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->valueCardDataHandleService = $valueCardDataHandleService;
        $this->categoryService = $categoryService;
    }

    /**
     * 获取当前用户订单可使用储值卡列表
     *
     * @param int $user_id 用户ID
     * @param array $cart_goods 购物车商品
     * @param int $vid 指定储值卡ID
     * @return array
     * @throws \Exception
     */
    public function getUserValueCard($user_id = 0, $cart_goods = [], $vid = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $sql = [
            'where' => [
                [
                    'name' => 'extension_code',
                    'value' => 'package_buy',
                    'condition' => '<>'
                ]
            ]
        ];
        $cart_goods = BaseRepository::getArraySqlGet($cart_goods, $sql);

        if (empty($cart_goods)) {
            return [];
        }

        $time = TimeRepository::getGmTime();

        $result = ValueCard::query()->select('vid', 'tid', 'end_time', 'card_money', 'value_card_sn', 'vc_dis')
            ->where('user_id', $user_id)
            ->where('card_money', '>', 0)
            ->where('use_status', 1)
            ->where('end_time', '>', $time);

        if ($vid > 0) {
            $result = $result->where('vid', $vid);
        }

        $result = BaseRepository::getToArrayGet($result);

        $tidList = BaseRepository::getKeyPluck($result, 'tid');

        $cardTypeList = ValueCardDataHandleService::getValueCardTypeDataList($tidList, ['id', 'name', 'vc_dis', 'vc_value', 'use_merchants', 'use_condition', 'spec_cat', 'spec_goods']);

        $spec_cat = BaseRepository::getKeyPluck($cardTypeList, 'spec_cat');

        $specCatList = [];
        if ($spec_cat) {
            foreach ($spec_cat as $key => $cats) {
                if (!empty($cats)) {
                    $cats = BaseRepository::getExplode($cats);
                    $cats = DscEncryptRepository::filterValInt($cats);
                    $specCatList[] = $cats;
                }
            }

            $specCatList = $specCatList ? BaseRepository::getArrayCollapse($specCatList) : [];

            if (!empty($specCatList)) {
                $specCatList = $this->categoryService->getCatListChildren($specCatList);
            }
        }

        $cartRuList = BaseRepository::getKeyPluck($cart_goods, 'ru_id');
        $cartGoodsList = BaseRepository::getKeyPluck($cart_goods, 'goods_id');

        if ($cardTypeList) {
            foreach ($cardTypeList as $key => $row) {

                $is_condition = 1; //默认满足条件

                /* 使用店铺 */
                if ($row['use_merchants'] == 'self') {
                    //购物流程是否含有自营店铺
                    if (!in_array(0, $cartRuList)) {
                        $is_condition = 0;
                    }
                } elseif ($row['use_merchants'] != 'all') {
                    $use_merchants = BaseRepository::getExplode($row['use_merchants']);
                    $intersectList = BaseRepository::getArrayIntersect($use_merchants, $cartRuList);
                    if (empty($intersectList)) {
                        $is_condition = 0;
                    }
                }

                if ($row['use_condition'] == 1) {

                    $specCat = BaseRepository::getExplode($row['spec_cat']);
                    $intersectCatsList = !empty($specCatList) && !empty($specCat) ? BaseRepository::getArrayIntersect($specCatList, $specCat) : [];

                    //分类条件
                    if (empty($specCatList) || empty($specCat) || empty($intersectCatsList)) {
                        $is_condition = 0;
                    }
                } elseif ($row['use_condition'] == 2) {

                    $specGoods = BaseRepository::getExplode($row['spec_goods']);
                    $intersectCatsList = !empty($cartGoodsList) && !empty($specGoods) ? BaseRepository::getArrayIntersect($cartGoodsList, $specGoods) : [];

                    //商品条件
                    if (empty($cartGoodsList) || empty($specGoods) || empty($intersectCatsList)) {
                        $is_condition = 0;
                    }
                }

                if ($is_condition == 0) {
                    unset($cardTypeList[$key]);
                } else {

                    $goods_list = [];
                    if ($row['use_merchants'] == 'self') {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'ru_id',
                                    'value' => 0
                                ]
                            ]
                        ];
                        $goods_list = BaseRepository::getArraySqlGet($cart_goods, $sql);
                    } elseif ($row['use_merchants'] != 'all') {

                        $ruIdList = BaseRepository::getExplode($row['use_merchants']);

                        if ($ruIdList) {
                            $sql = [
                                'whereIn' => [
                                    [
                                        'name' => 'ru_id',
                                        'value' => $ruIdList
                                    ]
                                ]
                            ];
                            $goods_list = BaseRepository::getArraySqlGet($cart_goods, $sql);
                        }
                    } else {
                        $goods_list = $cart_goods;
                    }

                    if ($row['use_condition'] == 1) { //分类

                        $useCatList = BaseRepository::getExplode($row['spec_cat']);
                        $useCatList = $this->categoryService->getCatListChildren($useCatList);

                        if ($useCatList) {
                            $sql = [
                                'whereIn' => [
                                    [
                                        'name' => 'cat_id',
                                        'value' => $useCatList
                                    ]
                                ]
                            ];
                            $goods_list = BaseRepository::getArraySqlGet($goods_list, $sql);
                        }
                    } elseif ($row['use_condition'] == 2) { //商品

                        $useGoodsList = BaseRepository::getExplode($row['spec_goods']);

                        if ($useGoodsList) {
                            $sql = [
                                'whereIn' => [
                                    [
                                        'name' => 'goods_id',
                                        'value' => $useGoodsList
                                    ]
                                ]
                            ];
                            $goods_list = BaseRepository::getArraySqlGet($cart_goods, $sql);
                        }
                    }

                    $cardTypeList[$key]['goods_list'] = $goods_list;
                }
            }
        }

        $tidList = BaseRepository::getKeyPluck($cardTypeList, 'id');

        if (empty($tidList)) {
            return [];
        }

        $sql = [
            'whereIn' => [
                [
                    'name' => 'tid',
                    'value' => $tidList
                ]
            ]
        ];
        $result = BaseRepository::getArraySqlGet($result, $sql);

        $arr = [];
        if (!empty($result)) {

            foreach ($result as $k => $v) {
                $cardType = $cardTypeList[$v['tid']] ?? [];
                $vc_dis = $cardType['vc_dis'] ?? 1;

                if (isset($v['vc_dis']) && $v['vc_dis'] > 0) {
                    $vc_dis = $v['vc_dis'] ?? 1;
                }

                $v = $cardType ? array_merge($v, $cardType) : $v;

                $v['vc_dis'] = $vc_dis;

                $arr[$k] = $v;
                $arr[$k]['vc_id'] = $v['vid'];
                $arr[$k]['name'] = $v['name'];
                $arr[$k]['card_money'] = $v['card_money'];
                $arr[$k]['card_money_formated'] = $this->dscRepository->getPriceFormat($v['card_money']);
                $arr[$k]['value_card_sn'] = $v['value_card_sn'];
                $arr[$k]['end_time'] = TimeRepository::getLocalDate("Y-m-d H:i:s", $v['end_time']);
                $vc_dis = $v['vc_dis'] == 1 ? 1 : $v['vc_dis'];
                $arr[$k]['card_discount'] = $vc_dis;
                $arr[$k]['vc_dis'] = $v['vc_dis'] == 1 ? '' : lang('user.discount_percent') . '：' . $this->dscRepository->changeFloat($v['vc_dis'] * 100) . lang('user.percent');

                if (empty($v['goods_list'])) {
                    unset($arr[$k]);
                }
            }
        }

        sort($arr);

        return $arr;
    }

    /**
     * 获取发放储值卡信息
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getValueCardInfo($where = [])
    {
        if (empty($where)) {
            return [];
        }

        $value_card = ValueCardRecord::selectRaw('*, vc_dis AS vcdis')->whereRaw(1);

        if (isset($where['order_id'])) {
            $value_card = $value_card->where('order_id', $where['order_id']);
        }

        $value_card = $value_card->with(['getValueCardType']);

        $value_card = $value_card->first();

        $value_card = $value_card ? $value_card->toArray() : [];

        $value_card = $value_card && $value_card['get_value_card_type'] ? array_merge($value_card, $value_card['get_value_card_type']) : $value_card;

        if ($value_card) {
            if ($value_card['vcdis'] > 0) {
                $value_card['vc_dis'] = $value_card['vcdis'];
            } else {
                $value_card['vc_dis'] = isset($value_card['get_value_card_type']['vc_dis']) ? $value_card['get_value_card_type']['vc_dis'] : 0;
            }
        }

        return $value_card;
    }

    /**
     * 取得储值卡信息
     *
     * @param int $value_card_id
     * @param string $value_card_psd
     * @param int $user_id
     * @return array
     */
    public function orderValueCardInfo($value_card_id = 0, $value_card_psd = '', $user_id = 0)
    {
        if (empty($value_card_id)) {
            return [];
        }

        $time = TimeRepository::getGmTime(); //当前时间

        $row = ValueCard::where('use_status', 1);

        if ($value_card_id > 0) {
            $row = $row->where('vid', $value_card_id)->where('user_id', $user_id);
        } else {
            $row = $row->where('value_card_password', $value_card_psd)
                ->where('user_id', 0);
        }

        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {

            /* 判断储值卡是否被版本，绑定时间是否过期 */
            if (!empty($row['end_time']) && $row['end_time'] > $time) {

                $row['admin_id'] = $row['user_id'];

                $valueCardType = ValueCardType::where('id', $row['tid']);
                $valueCardType = BaseRepository::getToArrayFirst($valueCardType);

                $row = BaseRepository::getArrayMerge($row, $valueCardType);
            } else {
                $row = [];
            }
        }

        return $row;
    }
}
